#!/usr/bin/env bash

ENC_KEY="symmetric_key.hex"
KEY_LEN=16

if [ -f "$ENC_KEY" ]; then
    echo "#########################################################################"
    echo "Symmetric key $ENC_KEY already exists. Please rm it before generating a new symmetric key!"
    echo "#########################################################################"
    exit 1
fi

echo "========================================================================="
echo "Generating a ${KEY_LEN}-byte symmetric key into $ENC_KEY"
echo "========================================================================="
openssl rand -hex -out $ENC_KEY $KEY_LEN

if [ $? -ne 0 ]; then
    echo "#########################################################################"
    echo "Generation of the encryption key went wrong, please ask the TA for assistance"
    echo "#########################################################################"
else
    echo "========================================================================="
    echo "Symmetric encryption key generated. You can now encrypt your secret message"
    echo "========================================================================="
fi

