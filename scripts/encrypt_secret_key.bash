#!/usr/bin/env bash

if [ $# -ne 1 ]; then
    echo "#########################################################################"
    echo "Usage: $0 <other_users_public_key_file>"
    echo "#########################################################################"
    exit 1
fi

OTHER_PUB_KEY="$1"
ENC_KEY="symmetric_key.hex"
PRIV_KEY="private_key.pem"
PUB_KEY="public_key.pem"

if [ ! -f "$OTHER_PUB_KEY" ]; then
    echo "#########################################################################"
    echo "Cannot access the file: $OTHER_PUB_KEY"
    echo "#########################################################################"
    exit 1
fi

if [ -z "$(diff $PUB_KEY $OTHER_PUB_KEY)" ]; then
    echo "#########################################################################"
    echo "The public key used to encrypt your symmetric key must be a different user's public key"
    echo "#########################################################################"
    exit 1
fi

if [ ! -f $ENC_KEY -o ! -f $PRIV_KEY -o ! -f $PUB_KEY ]; then
    echo "#########################################################################"
    echo "The following required files are missing - please double check your instructions:"
    if [ ! -f $ENC_KEY ]; then
        echo "  * $ENC_KEY"
    fi
    if [ ! -f $PRIV_KEY ]; then
        echo "  * $PRIV_KEY"
    fi
    if [ ! -f $PUB_KEY ]; then
        echo "  * $PUB_KEY"
    fi
    echo "#########################################################################"
    exit 1
fi

echo "========================================================================="
echo "Encrypting symmetric key $ENC_KEY with public key $OTHER_PUB_KEY"
echo "========================================================================="
openssl rsautl -encrypt -inkey $OTHER_PUB_KEY -pubin -in $ENC_KEY -out "encrypted_$ENC_KEY.$OTHER_PUB_KEY.enc"

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Encryption of the symmetric key went wrong, please ask the TA for assistance"
    echo "#########################################################################"
    exit 1
else
    echo "========================================================================="
    echo "Symmetric key encrypted and stored into: encrypted_$ENC_KEY.$OTHER_PUB_KEY.enc. You can now upload the encrypted symmetric key"
    echo "========================================================================="
fi

