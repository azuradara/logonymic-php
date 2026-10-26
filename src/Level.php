<?php

namespace Logonymic;

enum Level: string
{
    case Browser = 'browser';
    case Device = 'device';
    case Similar = 'similar';
    case Model = 'model';
    case None = 'none';
}
