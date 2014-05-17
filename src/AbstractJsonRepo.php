<?php

namespace CL\LunaJsonStore;

use CL\LunaCore\Repo\AbstractRepo;
use CL\LunaCore\Model\AbstractModel;
use SplObjectStorage;
use InvalidArgumentException;

/*
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
abstract class AbstractJsonRepo extends AbstractRepo
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
     * @param  string             $key
     * @return AbstractModel|null
     */
    public function selectWithId($key)
    {
        $contents = $this->getContents();

        if (isset($contents[$key])) {
            return $this->newInstance($contents[$key], AbstractModel::PERSISTED);
        }
    }

    /**
     * @return Select
     */
    public function findAll()
    {
        return new Select($this);
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

    public function update(SplObjectStorage $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            $contents[$model->getId()] = $model->getProperties();
        }

        $this->setContents($contents);
    }

    public function delete(SplObjectStorage $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            unset($contents[$model->getId()]);
        }

        $this->setContents($contents);
    }

    public function insert(SplObjectStorage $models)
    {
        $contents = $this->getContents();

        foreach ($models as $model) {
            $id = $contents ? max(array_keys($contents)) + 1 : 1;

            $model
                ->setId($id)
                ->resetOriginals()
                ->setState(AbstractModel::PERSISTED);

            $contents[$id] = $model->getProperties();
        }

        $this->setContents($contents);
    }
}
