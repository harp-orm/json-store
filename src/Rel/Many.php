<?php

namespace CL\LunaJsonStore\Rel;

use CL\LunaJsonStore\AbstractJsonRepo;
use CL\LunaCore\Rel\UpdateInterface;
use CL\LunaCore\Rel\AbstractRelMany;
use CL\LunaCore\Model\AbstractModel;
use CL\LunaCore\Repo\AbstractLink;
use CL\LunaCore\Repo\LinkMany;
use CL\Util\Arr;
use InvalidArgumentException;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Many extends AbstractRelMany implements UpdateInterface
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
     * @param  AbstractModel[]   $models
     * @return boolean
     */
    public function hasForeign(array $models)
    {
        $keys = Arr::pluckUniqueProperty($models, 'id');

        return ! empty($keys);
    }

    /**
     * @param  AbstractModel[]  $models
     * @return AbstractModel[]
     */
    public function loadForeign(array $models)
    {
        return $this->getForeignRepo()
            ->findAll()
            ->where($this->key, Arr::pluckUniqueProperty($models, 'id'))
            ->loadRaw();
    }

    /**
     * @param  AbstractModel $model
     * @param  AbstractLink  $link
     */
    public function update(AbstractModel $model, AbstractLink $link)
    {
        if (! ($link instanceof LinkMany)) {
            throw new InvalidArgumentException('Must use a LinkMany');
        }

        foreach ($link->getAdded() as $added) {
            $added->{$this->key} = $model->getId();
        }

        foreach ($link->getRemoved() as $added) {
            $added->{$this->key} = null;
        }
    }
}
