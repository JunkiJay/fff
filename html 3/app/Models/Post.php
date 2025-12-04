<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property string|null $owner_id
 * @property string|null $post_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Post extends Model
{
    protected $fillable = ['owner_id', 'post_id', 'type'];
}
