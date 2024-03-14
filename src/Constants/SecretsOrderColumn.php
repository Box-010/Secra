<?php

namespace Secra\Constants;

enum SecretsOrderColumn: string
{
  case ID = 'posts.post_id';
  case CREATED_AT = 'posts.created_at';
  case UPDATED_AT = 'posts.updated_at';
}
