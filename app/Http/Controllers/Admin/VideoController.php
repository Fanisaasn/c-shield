<?php
namespace App\Http\Controllers\Admin;
use App\Models\Video;
class VideoController extends ContentController {
    protected string $model = Video::class; protected string $view = 'videos'; protected string $route = 'videos'; protected ?string $uploadField = 'thumbnail'; protected ?string $slugField = 'slug';
    protected array $rules = ['title'=>['required','string','max:255'],'description'=>['nullable','string'],'video_url'=>['required','url','max:2048'],'thumbnail'=>['nullable','image','max:2048'],'is_published'=>['nullable','boolean'],'published_at'=>['nullable','date']];
}
