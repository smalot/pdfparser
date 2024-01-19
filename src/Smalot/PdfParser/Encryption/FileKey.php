<?php

/**
 * This file is based on code of tecnickcom/TCPDF PDF library.
 *
 * Original author Nicola Asuni (info@tecnick.com) and
 * contributors (https://github.com/tecnickcom/TCPDF/graphs/contributors).
 *
 * @see https://github.com/tecnickcom/TCPDF
 *
 * Original code was licensed on the terms of the LGPL v3.
 *
 * ------------------------------------------------------------------------------
 *
 * @file This file is part of the PdfParser library.
 *
 * @author  Alastair Irvine <alastair@plug.org.au>
 *
 * @date    2024-01-12
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Smalot\PdfParser\Encryption;

/**
 * Creates the file's decryption key from the info about the file and
 * optionally the owner and/or user password.  Doesn't call
 * Info::getEncAlgorithm(), but figures out what to do based on the encryption
 * version and revision instead.
 */
abstract class FileKey
{
    function __construct(Info $info)
    {
        $this->info = $info;
    }


    public function getKey()
    {
        return $this->fileKey;
    }


    /**
     * Creates the file's decryption key.  Internally, creates an instance of
     * the appropriate child class.
     *
     * @return byte string
     */
    public static function generate(Info $info, $ownerPassword = null, $userPassword = null)
    {
        // Create an instance of the appropriate child class.
        switch ($info->getRevision()) {
            case 2:
            case 3:
                $helper = new OldFileKey($info, $ownerPassword, $userPassword);
                break;

            case 5:
            case 6:
                $helper = new NewFileKey($info, $ownerPassword, $userPassword);
                break;

            default:
                throw new InvalidRevision("Unsupported revision in makeFileKey()");
        }

        return $helper->getKey();
    }
}


/**
 * Handles file keys for encryption revisions 2 and 3.
 */
class OldFileKey extends FileKey
{
    /**
     * @throws InvalidPassword if neither of the supplied passwords are valid
     */
    function __construct(Info $info, $ownerPassword = null, $userPassword = null)
    {
        parent::__construct($info);

        $passwordPaddingBytes = [ 0x28, 0xbf, 0x4e, 0x5e, 0x4e, 0x75, 0x8a, 0x41, 0x64, 0x00, 0x4e, 0x56, 0xff, 0xfa, 0x01, 0x08, 0x2e, 0x2e, 0x00, 0xb6, 0xd0, 0x68, 0x3e, 0x80, 0x2f, 0x0c, 0xa9, 0xfe, 0x64, 0x53, 0x69, 0x7a ];
        $this->passwordPadding = \implode(\array_map("chr", $passwordPaddingBytes));

        if (!empty($ownerPassword) && \strlen($ownerPassword) > 32) {
            $this->ownerPassword = \substr($ownerPassword, 0, 32);
        } else {
            $this->ownerPassword = $ownerPassword;
        }
        if (!empty($userPassword) && \strlen($userPassword) > 32) {
            $this->userPassword = \substr($userPassword, 0, 32);
        } else {
            $this->userPassword = $userPassword;
        }

        if (!\is_null($this->ownerPassword)) {
            $password = $this->decryptPassword($this->ownerPassword);
            $this->fileKey = $this->makeFileKeyOld($password);
            if (!$this->testFileKey($this->fileKey)) {
                $this->fileKey = null;
            }
        } else {
            $this->fileKey = null;
        }

        // If owner password was invalid, try user password
        if (\is_null($this->fileKey)) {
            $password = \is_null($this->userPassword) ? "" : $this->userPassword;
            $this->fileKey = $this->makeFileKeyOld($password);
            if (!$this->testFileKey($this->fileKey))
                throw new InvalidPassword();
        }
    }


    /**
     * Generate the user password by creating a key by hashing the supplied
     * owner password and using it to decrypt the owner key from the file data.
     */
    function decryptPassword(string $password)
    {
        if (\strlen($password) >= 32) {
            $data = \substr($password, 0, 32);
        } else {
            $data = $password.\substr($this->passwordPadding, 0, 32 - \strlen($password));
        }
        $hash = \md5($data, true);

        switch ($this->info->getRevision()) {
            case 2:
                // Try to decrypt the hashed user password and see if matches the padding string
                return \openssl_decrypt($this->info->getOwnerKey(), "RC4-40", $hash, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
                break;

            case 3:
                for ($round = 0; $round < 50; ++$round) {
                    $hash = \md5($hash, true);
                }
                return $this->magicDecrypt($this->info->getOwnerKey(), $hash);
                break;
        }
    }


    function makeFileKeyOld($password)
    {
        $permBytes = \Smalot\PdfParser\Utils::lowestBytesStr($this->info->getPerms(), 4);
        $padding = \substr($this->passwordPadding, 0, 32 - \strlen($password));
        $data = $password.$padding.$this->info->getOwnerKey().$permBytes.$this->info->getDocID();
        $len = \strlen($data);
        if (!$this->info->getEncryptMetadata()) {
            $data .= \Smalot\PdfParser\Utils::lowestBytesStr(-1, 4);
        }

        $hash = \md5($data, true);
        if ($this->info->getRevision() == 3) {
            for ($round = 0; $round < 50; ++$round) {
                $hash = \md5($hash, true);
            }
        }
        return \substr($hash, 0, $this->info->getFileKeyLength());
    }


    /**
     * Check that the file key is valid.
     *
     * @return bool
     */
    function testFileKey(string $key)
    {
        switch ($this->info->getRevision()) {
            case 2:
                // Try to decrypt the hashed user password and see if matches the padding string
                $plaintext = \openssl_decrypt($this->info->getUserKey(), "RC4-40", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
                return $plaintext === $this->passwordPadding;
                break;

            case 3:
                // Try to decrypt the hashed user password 20 times using a XOR
                // cycling of the key and see if matches the padding string
                $data = $this->magicDecrypt($this->info->getUserKey(), $key);
                $hash = \md5($this->passwordPadding.$this->info->getDocID(), true);
                return \substr($data, 0, 16) == $hash;
                break;

            default:
                throw new InvalidRevision("Mismatch between caller and testPasswordOld()");
        }
    }


    /**
     * Multi-round basic decryption used by revision 3.
     *
     * @return byte string
     */
    function magicDecrypt(string $data, string $key)
    {
        for ($i = 19; $i >= 0; --$i) {
            $roundKey = \implode(\array_map(
                function($c) use ($i) { return \chr(\ord($c) ^ $i); },
                \str_split($key)
            ));
            $data = \openssl_decrypt($data, "RC4-40", $roundKey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        }
        return $data;
    }
}


/**
* Handles file keys for encryption revisions 5 and 6.
*/
class NewFileKey extends FileKey
{
    /**
     * @throws InvalidPassword if neither of the supplied passwords are valid
     */
    function __construct(Info $info, $ownerPassword = null, $userPassword = null)
    {
        parent::__construct($info);

        if (!empty($ownerPassword) && \strlen($ownerPassword) > 127) {
            $this->ownerPassword = \substr($ownerPassword, 0, 127);
        } else {
            $this->ownerPassword = $ownerPassword;
        }
        if (!empty($userPassword) && \strlen($userPassword) > 127) {
            $this->userPassword = \substr($userPassword, 0, 127);
        } else {
            $this->userPassword = $userPassword;
        }

        // Revision 5 and 6 keys are 48 bytes long
        //   Bytes 0-31: password hash
        //   Bytes 32-39: password check hash salt
        //   Bytes 40-47: file key hash salt
        // Note that when using the owner password, the whole user key
        // is also included in the hash input when checking the
        // password and decrypting the file key (which is encrypted
        // twice, using different intermediate keys, in ownerEnc and
        // userEnc)
        if (!\is_null($this->ownerPassword)) {
            $mainKey = $this->info->getOwnerKey();
            $additionalKey = $this->info->getUserKey();
            $password = $this->ownerPassword;
            $encryptedFileKey = $this->info->getOwnerEnc();
            $passwordHash = $this->hashPassword($password, $mainKey, $additionalKey);
            if (!$this->testPasswordHash($passwordHash, $mainKey)) {
                $passwordHash = null;
            }
        } else {
            $passwordHash = null;
        }

        if (\is_null($passwordHash)) {
            $mainKey = $this->info->getUserKey();
            $additionalKey = "";
            $password = \is_null($this->userPassword) ? "" : $this->userPassword;
            $encryptedFileKey = $this->info->getUserEnc();
            $passwordHash = $this->hashPassword($password, $mainKey, $additionalKey);
            if (!$this->testPasswordHash($passwordHash, $mainKey)) {
                throw new InvalidPassword();
            }
        }

        $this->fileKey = $this->makeFileKeyNew($password, $mainKey, $additionalKey, $encryptedFileKey);
    }


    /**
     * Make a hash that can be used to check whether a given password matches
     * the one the file was created with.
     *
     * @param $password         Owner or user password
     * @param $mainKey
     * @param $additionalKey    0 or 48 byte string (when using owner password)
     * @return 32 byte string
     */
    function hashPassword(string $password, string $mainKey, string $additionalKey)
    {
        // Use first half of latter 16 bytes of $mainKey and all of
        // $additionalKey (if any)
        return $this->intermediateKey($password, \substr($mainKey, 32, 8), $additionalKey);
    }


    /**
     * Check the hash against the first 32 bytes of the key.
     *
     * @return bool
     */
    function testPasswordHash(string $passwordHash, string $key)
    {
        return \substr($key, 0, 32) == $passwordHash;
    }


    /**
     * Decrypt the relevant ...Enc field.
     *
     * @param $password         Owner or user password
     * @param $mainKey
     * @param $additionalKey    0 or 48 byte string (when using owner password)
     * @param $encryptedFileKey 32 byte string
     *
     * @return 32 byte string
     *
     * @throws DecryptionError if the password can't decrypt the file key; shouldn't be possible
     */
    function makeFileKeyNew($password, $mainKey, $additionalKey, $encryptedFileKey)
    {
        // Use second half of latter 16 bytes of $mainKey and all of
        // $additionalKey (if any)
        $key = $this->intermediateKey($password, \substr($mainKey, 40, 8), $additionalKey);
        $iv = \Smalot\PdfParser\Utils::byteString(16);
        $decryptedKey = \openssl_decrypt($encryptedFileKey, "aes-256-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        if ($decryptedKey === false) {
            throw new DecryptionError("Decryption failed");
        }
        return $decryptedKey;
    }


    /**
     * Generate a hash that can either be used to decrypt the relevant ...Enc
     * field and get the file key, or verify the password.
     *
     * @param $password         Owner or user password
     * @param $saltKey          8 byte string: part of the corresponding key
     * @param $additionalKey    0 or 48 byte string (when using owner password)
     * @return 32 byte string
     */
    function intermediateKey($password, $saltKey, $additionalKey)
    {
        $hashInput = $password.$saltKey.$additionalKey;

        switch ($this->info->getRevision()) {
            case 5:
                return \hash("sha256", $hashInput, true);
                break;

            case 6:
                /* Each round encrypts a plaintext comprising 64 copies of the
                 * input data plus the hash of the previous round, using a key
                 * and IV derived from the hash of the previous round.  Then a
                 * hash of that round's cyphertext is produced using a
                 * different hash algorithm (selected based on the cyphertext).
                 * The number of rounds is not fixed; the decision to continue
                 * is based on the cyphertext (see PDFDecryptionKey::hashContinue()).
                 * 32 bytes of the hash produced by the final round is returned.
                 */

                // initial hash
                $hash = \hash("sha256", $hashInput, true);

                $this->round = 0;
                do {
                    $hashInput = $password.$hash.$additionalKey;
                    $blob = "";
                    for ($i = 0; $i < 64; ++$i) {
                        $blob .= $hashInput;
                    }
                    $key = \substr($hash, 0, 16);
                    $iv = \substr($hash, 16, 16);

                    // This is different from the key decryption algorithm
                    $this->cyphertext = \openssl_encrypt($blob, "aes-128-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
                    // Derive some 64 bit numbers from the cyphertext and
                    // analyse them to determine the hash algorithm to use this
                    // round
                    $splinters = self::extractSplinters($this->cyphertext);
                    $remainder = (((((($splinters[0] % 3) << 32) | $splinters[1]) % 3) << 32) | $splinters[2]) % 3;
                    switch ($remainder) {
                        case 0:
                            $hash = \hash("sha256", $this->cyphertext, true);
                            break;

                        case 1:
                            $hash = \hash("sha384", $this->cyphertext, true);
                            break;

                        case 2:
                            $hash = \hash("sha512", $this->cyphertext, true);
                            break;
                    }
                    ++$this->round;
                } while ($this->hashContinue());

                return \substr($hash, 0, 32);
                break;

            default:
                throw new InvalidRevision("Unsupported revision in intermediateKey()");
        }
    }


    /**
     * Determine whether to continue based on the round count and an arbitrary
     * value within the current round's cyphertext.  A minimum of 64 and a
     * maximum of 288 rounds will be done.
     *
     * @return boolean
     */
    function hashContinue()
    {
        $lastByte = \ord(\substr($this->cyphertext, -1));
        $currentLimit = \max(64, $lastByte + 32);
        return $this->round < $currentLimit;
    }


    /**
     * Derive 3 integers from the first 16 bytes of $input.
     * Bytes are treated as being unsigned, in big-endian order.
     *
     * @input a byte string of at least 16 characters
     *
     * @return array of 3 integers
     */
    static function extractSplinters($input)
    {
        $result = [];
        $result[] = \hexdec(\bin2hex(\substr($input, 0, 8)));
        $result[] = \hexdec(\bin2hex(\Smalot\PdfParser\Utils::byteString(4).\substr($input, 8, 4)));
        $result[] = \hexdec(\bin2hex(\Smalot\PdfParser\Utils::byteString(4).\substr($input, 12, 4)));
        return $result;
    }
}
