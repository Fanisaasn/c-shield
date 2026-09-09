<?php
namespace App\Http\Controllers\Admin;
use App\Models\Webinar;
class WebinarController extends ContentController {
    protected string $model = Webinar::class; protected string $view = 'webinars'; protected string $route = 'webinars'; protected ?string $uploadField = 'poster_image';
    protected array $rules = ['title'=>['required','string','max:255'],'description'=>['nullable','string'],'speaker'=>['nullable','string','max:255'],'webinar_date'=>['required','date'],'platform'=>['nullable','string','max:255'],'registration_url'=>['required','url','max:2048'],'poster_image'=>['nullable','image','max:4096'],'is_published'=>['nullable','boolean']];
}
