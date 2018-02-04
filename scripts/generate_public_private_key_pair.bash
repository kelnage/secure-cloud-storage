#!/usr/bin/env bash

PRIV_KEY="private_key.pem"
PUB_KEY="public_key.pem"
KEY_LEN="2048"

if [ -f "$PRIV_KEY" ]; then
    echo "#########################################################################"
    echo "Private key $PRIV_KEY already exists. Please rm it before generating a new key pair!"
    echo "#########################################################################"
    exit 1
fi

echo "========================================================================="
echo "Generating a ${KEY_LEN}-bit private key into $PRIV_KEY"
echo "========================================================================="
openssl genpkey -algorithm RSA -out $PRIV_KEY -pkeyopt rsa_keygen_bits:$KEY_LEN

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Generation of the private key went wrong, please ask the TA for assistance"
    echo "#########################################################################"
fi

echo "========================================================================="
echo "Extracting a public key from $PRIV_KEY into $PUB_KEY"
echo "========================================================================="
openssl rsa -pubout -in $PRIV_KEY -out $PUB_KEY

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Generation of the public key went wrong, please ask the TA for assistance"
    echo "#########################################################################"
else
    echo "========================================================================="
    echo "Private and public keys generated. You can now upload $PUB_KEY to the messaging server"
    echo "========================================================================="
fi

