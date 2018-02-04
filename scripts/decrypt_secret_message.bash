#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "#########################################################################"
    echo "Usage: $0 <encrypted_symmetric_key> <encrypted_msg_file>"
    echo "#########################################################################"
    exit 1
fi

SECRET_KEY=$1
SECRET_MSG=$2
PRIV_KEY="private_key.pem"
PUB_KEY="public_key.pem"

if [ ! -f $PRIV_KEY -o ! -f $PUB_KEY ]; then
    echo "#########################################################################"
    echo "The following required files are missing - please double check your instructions:"
    if [ ! -f $PRIV_KEY ]; then
        echo "  * $PRIV_KEY"
    fi
    if [ ! -f $PUB_KEY ]; then
        echo "  * $PUB_KEY"
    fi
    echo "#########################################################################"
    exit 1
fi

if [ ! -f "$SECRET_KEY" -o ! -f "$SECRET_MSG" ]; then
    echo "#########################################################################"
    echo "The following encrypted files are missing:"
    if [ ! -f "$SECRET_KEY" ]; then
        echo "  * $SECRET_KEY"
    fi
    if [ ! -f "$SECRET_MSG" ]; then
        echo "  * $SECRET_MSG"
    fi
    echo "#########################################################################"
    exit 1
fi


echo "========================================================================="
echo "Decrypting symmetric key $SECRET_KEY with private key $PRIV_KEY and storing it into: $SECRET_KEY.dec"
echo "========================================================================="
openssl rsautl -decrypt -inkey $PRIV_KEY -in "$SECRET_KEY" -out "$SECRET_KEY.dec"

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Decryption of the symmetric key went wrong, please ask the TA for assistance"
    echo "#########################################################################"
    exit 1
fi

echo "========================================================================="
echo "Decrypting secret message $SECRET_MSG with symmetric key $SECRET_KEY.dec"
echo "========================================================================="
openssl enc -aes-128-cbc -d -K $(< "$SECRET_KEY.dec") -iv "0000" -in "$SECRET_MSG" -out "$SECRET_MSG.txt"

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Decryption of the message went wrong, please ask the TA for assistance"
    echo "#########################################################################"
    exit 1
else
    echo "========================================================================="
    echo "The secret message has been successfully decrypted into: $SECRET_MSG.txt"
    echo "========================================================================="
fi

