<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Andreas Gohr <gohr@cosmocode.de>
 *
 * @date    2026-06-18
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

namespace Smalot\PdfParser;

/**
 * Temporary on-disk store for decoded stream content.
 *
 * When content spooling is enabled (see Config::setContentSpooling()), the
 * decoded content of each parsed object is appended to a single temporary file
 * instead of being held in memory, and read back on demand. This trades a small
 * amount of disk I/O for a markedly lower peak memory footprint, since the full
 * set of decoded streams - usually the largest thing a parsed Document keeps
 * alive - no longer has to reside in RAM all at once.
 *
 * The backing temporary file is created lazily on first use and removed
 * automatically once the spool (and the Document owning it) is destroyed.
 *
 * @internal
 */
class ContentSpool
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * Current size of the spool file, i.e. the offset at which the next chunk
     * of content will be written.
     *
     * @var int
     */
    private $size = 0;

    /**
     * Append a chunk of content to the spool.
     *
     * @return array{0: int, 1: int}|null [offset, length] locating the stored
     *                                     content, or null if it could not be
     *                                     stored (the caller should then keep
     *                                     the content in memory)
     */
    public function store(string $content): ?array
    {
        $length = \strlen($content);
        if (0 === $length) {
            return null;
        }

        $handle = $this->handle();
        if (null === $handle) {
            return null;
        }

        $offset = $this->size;
        if (0 === fseek($handle, $offset) && fwrite($handle, $content) === $length) {
            $this->size += $length;

            return [$offset, $length];
        }

        return null;
    }

    /**
     * Read back content previously stored via store().
     *
     * @param int $offset offset returned by store()
     * @param int $length length returned by store()
     */
    public function fetch(int $offset, int $length): string
    {
        if (!\is_resource($this->handle) || $length <= 0) {
            return '';
        }

        // Seeking before reading also flushes any pending write buffer, which
        // is required when reading data that was just written to the handle.
        if (0 !== fseek($this->handle, $offset)) {
            return '';
        }

        $content = '';
        $remaining = $length;
        while ($remaining > 0 && !feof($this->handle)) {
            $chunk = fread($this->handle, $remaining);
            if (false === $chunk || '' === $chunk) {
                break;
            }
            $content .= $chunk;
            $remaining -= \strlen($chunk);
        }

        return $content;
    }

    /**
     * Lazily open the backing temporary file.
     *
     * @return resource|null
     */
    private function handle()
    {
        if (null === $this->handle) {
            // tmpfile() opens a binary-safe read-write handle and removes the
            // underlying file automatically when the handle is closed.
            $handle = tmpfile();
            $this->handle = false !== $handle ? $handle : null;
        }

        return $this->handle;
    }

    /**
     * Ensure the backing file is closed and removed when the spool is destroyed.
     */
    public function __destruct()
    {
        if (\is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
