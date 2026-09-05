<?php
namespace App\Http\Controllers\Admin;
use App\Models\Flyer;
class FlyerController extends ContentController {
    protected string $model = Flyer::class; protected string $view = 'flyers'; protected string $route = 'flyers'; protected ?string $uploadField = 'image';
    protected array $rules = ['title'=>['required','string','max:255'],'description'=>['nullable','string'],'image'=>['nullable','image','max:4096'],'is_published'=>['nullable','boolean'],'published_at'=>['nullable','date']];
}
