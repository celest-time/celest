<?php

namespace Celest\Temporal;

use Celest\Helper\Long;

abstract class AbstractTemporal extends AbstractTemporalAccessor implements Temporal
{
    /**
     * @inheritdoc
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        return $adjuster->adjustInto($this);
    }

    /**
     * @inheritdoc
     */
    public function plusAmount(TemporalAmount $amount)
    {
        return $amount->addTo($this);
    }

    /**
     * @inheritdoc
     */
    public function minusAmount(TemporalAmount $amount)
    {
        return $amount->subtractFrom($this);
    }

    /**
     * @inheritdoc
     */
    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract === Long::MIN_VALUE ? $this->plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $this->plus(-$amountToSubtract, $unit));
    }

}
