#!/usr/bin/env bash

if [ $# -ne 1 ]; then
    echo "#########################################################################"
    echo "Usage: $0 <msg_file|message>"
    echo "#########################################################################"
    exit 1
fi

SECRET_MSG=$1
ENC_KEY="symmetric_key.hex"
PRIV_KEY="private_key.pem"
PUB_KEY="public_key.pem"

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

if [ -f "$SECRET_MSG" ]; then
    echo "========================================================================="
    echo "Encrypting message file $SECRET_MSG with secret key $ENC_KEY and storing it into: $SECRET_MSG.enc"
    echo "========================================================================="
    openssl enc -aes-128-cbc -K $(< $ENC_KEY) -iv "0000" -in $SECRET_MSG -out $SECRET_MSG.enc
    if [ $? -ne 0 ]; then
        echo "#########################################################################"
        echo "Encryption of the secret message went wrong, please ask the TA for assistance"
        echo "#########################################################################"
        exit 1
    fi
else
    echo "========================================================================="
    echo "Encrypting message '$SECRET_MSG' with secret key $ENC_KEY and storing it into: encrypted_message.bin"
    echo "========================================================================="
    tmpfile=$(mktemp) 
    echo "$SECRET_MSG" > $tmpfile
    openssl enc -aes-128-cbc -K $(< $ENC_KEY) -iv "0000" -in $tmpfile -out encrypted_message.bin
    if [ $? -ne 0 ]; then
        echo "#########################################################################"
        echo "Encryption of the secret message went wrong, please ask the TA for assistance"
        echo "#########################################################################"
        rm $tmpfile
        exit 1
    fi
    rm $tmpfile
fi
