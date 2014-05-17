<?php namespace Amegatron\Cryptoapi\KeyGenerators;

class OpenSslKeyGenerator implements KeyGeneratorInterface {

    public function generateKeyPair($keyPath, $keySize)
    {
        // Fill in data for the distinguished name to be used in the cert
        // You must change the values of these keys to match your name and
        // company, or more precisely, the name and company of the person/site
        // that you are generating the certificate for.
        // For SSL certificates, the commonName is usually the domain name of
        // that will be using the certificate, but for S/MIME certificates,
        // the commonName will be the name of the individual who will use the
        // certificate.
        $dn = array(
            "countryName" => "UK",
            "stateOrProvinceName" => "Somerset",
            "localityName" => "Glastonbury",
            "organizationName" => "The Brain Room Limited",
            "organizationalUnitName" => "PHP Documentation Team",
            "commonName" => "Wez Furlong",
            "emailAddress" => "wez@example.com"
        );

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new(array(
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ));

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey);

        // You will usually want to create a self-signed certificate at this
        // point until your CA fulfills your request.
        $sscert = openssl_csr_sign($csr, null, $privkey, 365000);

        // Now you will want to preserve your private key, CSR and self-signed
        // cert so that they can be installed into your web server, mail server
        // or mail client (depending on the intended use of the certificate).
        // This example shows how to get those things into variables, but you
        // can also store them directly into files.
        // Typically, you will send the CSR on to your CA who will then issue
        // you with the "real" certificate.
        // openssl_csr_export($csr, $csrout);
        // echo $csrout . "\n";
        openssl_x509_export($sscert, $certout);
        file_put_contents($keyPath . '/public.crt', $certout);
        openssl_pkey_export($privkey, $pkeyout, "mypassword");
        file_put_contents($keyPath . '/private.key', $pkeyout);
    }
}
