<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter;

interface FormatterInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function format($value): string;
}
