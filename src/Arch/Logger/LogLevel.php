<?php

namespace Secra\Arch\Logger;


enum LogLevel: int
{
  case FATAL = 0;
  case ERROR = 1;
  case WARN = 2;
  case INFO = 3;
  case DEBUG = 4;
  case TRACE = 5;
}
