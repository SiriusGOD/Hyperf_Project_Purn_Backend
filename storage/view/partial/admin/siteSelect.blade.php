<div class="form-group">
    <label for="exampleInputEmail1">{{trans('default.attribution_web') ?? '歸屬網站'}}</label>
    <select class="form-control form-control-lg" name="site_id">
        @foreach(make(\App\Service\SiteService::class)->getSiteModels() as $site)
        <option value="{{$site->id}}" {{($model->site_id ?? '') == $site->id ? 'selected' : ''}}>
            {{$site->name}}
        </option>
        @endforeach
    </select>
</div>