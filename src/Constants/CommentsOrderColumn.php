<?php

namespace Secra\Constants;

enum CommentsOrderColumn : string
{
  case ID = 'comments.comment_id';
  case CREATED_AT = 'comments.created_at';
  case UPDATED_AT = 'comments.updated_at';

  case PARENT_ID = 'comments.parent_id';
}
