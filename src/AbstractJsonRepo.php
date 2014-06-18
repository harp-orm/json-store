<?php

namespace Harp\JsonStore;

use Harp\Core\Save\AbstractSaveRepo;
use Harp\Core\Model\Models;
use InvalidArgumentException;

/*
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
abstract class AbstractJsonRepo extends AbstractSaveRepo
{
    /**
     * @var string
     */
    private $file;

    /**
     * @param string $modelClass
     */
    public function __construct($modelClass, $file)
    {
        parent::__construct($modelClass);

        $dir = dirname($file);

        if (! is_dir($dir) or ! is_writable($dir)) {
            throw new InvalidArgumentException('file dir must exist and be writable');
        }

        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return Find
     */
    public function findAll()
    {
        return new Find($this);
    }

    /**
     * @return array
     */
    public function getContents()
    {
        return json_decode(file_get_contents($this->file), true);
    }

    /**
     * @param array $contents
     */
    public function setContents(array $contents)
    {
        file_put_contents($this->file, json_encode($contents, JSON_PRETTY_PRINT));

        return $this;
    }

    public function update(Models $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            $contents[$model->getId()] = $this->serializeModel($model->getProperties());
        }

        $this->setContents($contents);
    }

    public function delete(Models $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            unset($contents[$model->getId()]);
        }

        $this->setContents($contents);
    }

    public function insert(Models $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            $id = $contents ? max(array_keys($contents)) + 1 : 1;

            $contents[$id] = $this->serializeModel($model->setId($id)->getProperties());
        }

        $this->setContents($contents);
    }
}
