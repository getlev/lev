<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

use Lev\Common\File\CompiledYamlFile;
use Lev\Common\Lev;
use Lev\Common\Data\Data;
use Lev\Common\Data\Blueprint;
use Lev\Common\Media\Interfaces\MediaFileInterface;
use Lev\Common\Media\Interfaces\MediaLinkInterface;
use Lev\Common\Media\Traits\MediaFileTrait;
use Lev\Common\Media\Traits\MediaObjectTrait;

/**
 * Class Medium
 * @package Lev\Common\Page\Medium
 *
 * @property string $filepath
 * @property string $filename
 * @property string $basename
 * @property string $mime
 * @property int $size
 * @property int $modified
 * @property array $metadata
 * @property int|string $timestamp
 */
class Medium extends Data implements RenderableInterface, MediaFileInterface
{
    use MediaObjectTrait;
    use MediaFileTrait;
    use ParsedownHtmlTrait;

    /**
     * Construct.
     *
     * @param array $items
     * @param Blueprint|null $blueprint
     */
    public function __construct($items = [], Blueprint $blueprint = null)
    {
        parent::__construct($items, $blueprint);

        if (Lev::instance()['config']->get('system.media.enable_media_timestamp', true)) {
            $this->timestamp = Lev::instance()['cache']->getKey();
        }

        $this->def('mime', 'application/octet-stream');

        if (!$this->offsetExists('size')) {
            $path = $this->get('filepath');
            $this->def('size', filesize($path));
        }

        $this->reset();
    }

    /**
     * Clone medium.
     */
    #[\ReturnTypeWillChange]
    public function __clone()
    {
        // Allows future compatibility as parent::__clone() works.
    }

    /**
     * Add meta file for the medium.
     *
     * @param string $filepath
     */
    public function addMetaFile($filepath)
    {
        $this->metadata = (array)CompiledYamlFile::instance($filepath)->content();
        $this->merge($this->metadata);
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return [
            'mime' => $this->mime,
            'size' => $this->size,
            'modified' => $this->modified,
        ];
    }

    /**
     * Return string representation of the object (html).
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function __toString()
    {
        return $this->html();
    }

    /**
     * @param string $thumb
     * @return Medium|null
     */
    protected function createThumbnail($thumb)
    {
        return MediumFactory::fromFile($thumb, ['type' => 'thumbnail']);
    }

    /**
     * @param array $attributes
     * @return MediaLinkInterface
     */
    protected function createLink(array $attributes)
    {
        return new Link($attributes, $this);
    }

    /**
     * @return Lev
     */
    protected function getLev(): Lev
    {
        return Lev::instance();
    }

    /**
     * @return array
     */
    protected function getItems(): array
    {
        return $this->items;
    }
}
