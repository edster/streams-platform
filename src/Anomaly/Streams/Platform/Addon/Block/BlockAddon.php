<?php namespace Anomaly\Streams\Platform\Addon\Block;

use Anomaly\Streams\Platform\Addon\Addon;
use Anomaly\Streams\Platform\Contract\PresentableInterface;

class BlockAddon extends Addon implements PresentableInterface
{
    public function decorate()
    {
        return new BlockPresenter($this);
    }
}
