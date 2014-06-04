<?php

namespace Harp\JsonStore\Rel;

use Harp\JsonStore\AbstractJsonRepo;
use Harp\Core\Rel\AbstractRelMany;
use Harp\Core\Rel\UpdateManyInterface;
use Harp\Core\Model\AbstractModel;
use Harp\Core\Model\Models;
use Harp\Core\Repo\LinkMany;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Many extends AbstractRelMany implements UpdateManyInterface
{
    /**
     * Its protected so it can be modified by the parent class
     * @var string
     */
    protected $key;

    public function __construct(
        $name,
        AbstractJsonRepo $repo,
        AbstractJsonRepo $foreignRepo,
        array $options = array()
    ) {
        $this->key = lcfirst($repo->getName()).'Id';

        parent::__construct($name, $repo, $foreignRepo, $options);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  AbstractModel $model
     * @param  AbstractModel $foreign
     * @return boolean
     */
    public function areLinked(AbstractModel $model, AbstractModel $foreign)
    {
        return $model->getId() == $foreign->{$this->key};
    }

    /**
     * @param  Models  $models
     * @return boolean
     */
    public function hasForeign(Models $models)
    {
        $keys = array_filter($models->pluckProperty($this->getRepo()->getPrimaryKey()));

        return ! empty($keys);
    }

    /**
     * @param  Models
     * @return AbstractModel[]
     */
    public function loadForeign(Models $models, $flags = null)
    {
        $keys = array_filter($models->pluckProperty($this->getRepo()->getPrimaryKey()));

        return $this->getForeignRepo()
            ->findAll()
            ->whereIn($this->key, $keys)
            ->loadRaw($flags);
    }

    /**
     * @param LinkMany      $link
     */
    public function update(LinkMany $link)
    {
        foreach ($link->getAdded() as $added) {
            $added->{$this->key} = $link->getModel()->getId();
        }

        foreach ($link->getRemoved() as $added) {
            $added->{$this->key} = null;
        }
    }
}
