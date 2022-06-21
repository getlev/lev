<?php

/**
 * @package    Lev\Grav\Common\Form
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Form;

use Lev\Common\Filesystem\Folder;
use Lev\Common\Utils;
use Lev\Framework\Form\FormFlash as FrameworkFormFlash;
use function is_array;

/**
 * Class FormFlash
 * @package Lev\Common\Form
 */
class FormFlash extends FrameworkFormFlash
{
    /**
     * @return array
     * @deprecated 1.6 For backwards compatibility only, do not use
     */
    public function getLegacyFiles(): array
    {
        $fields = [];
        foreach ($this->files as $field => $files) {
            if (strpos($field, '/')) {
                continue;
            }
            foreach ($files as $file) {
                if (is_array($file)) {
                    $file['tmp_name'] = $this->getTmpDir() . '/' . $file['tmp_name'];
                    $fields[$field][$file['path'] ?? $file['name']] = $file;
                }
            }
        }

        return $fields;
    }

    /**
     * @param string $field
     * @param string $filename
     * @param array $upload
     * @return bool
     * @deprecated 1.6 For backwards compatibility only, do not use
     */
    public function uploadFile(string $field, string $filename, array $upload): bool
    {
        if (!$this->uniqueId) {
            return false;
        }

        $tmp_dir = $this->getTmpDir();
        Folder::create($tmp_dir);

        $tmp_file = $upload['file']['tmp_name'];
        $basename = Utils::basename($tmp_file);

        if (!move_uploaded_file($tmp_file, $tmp_dir . '/' . $basename)) {
            return false;
        }

        $upload['file']['tmp_name'] = $basename;
        $upload['file']['name'] = $filename;

        $this->addFileInternal($field, $filename, $upload['file']);

        return true;
    }

    /**
     * @param string $field
     * @param string $filename
     * @param array $upload
     * @param array $crop
     * @return bool
     * @deprecated 1.6 For backwards compatibility only, do not use
     */
    public function cropFile(string $field, string $filename, array $upload, array $crop): bool
    {
        if (!$this->uniqueId) {
            return false;
        }

        $tmp_dir = $this->getTmpDir();
        Folder::create($tmp_dir);

        $tmp_file = $upload['file']['tmp_name'];
        $basename = Utils::basename($tmp_file);

        if (!move_uploaded_file($tmp_file, $tmp_dir . '/' . $basename)) {
            return false;
        }

        $upload['file']['tmp_name'] = $basename;
        $upload['file']['name'] = $filename;

        $this->addFileInternal($field, $filename, $upload['file'], $crop);

        return true;
    }
}
