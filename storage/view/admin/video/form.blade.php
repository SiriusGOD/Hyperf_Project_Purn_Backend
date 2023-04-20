@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/video/store" method="post" class="col-md-12">

                                @if($video->id)
                                    <input type="hidden" name="id" value="{{$video->id}}">
                                @endif

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.title') ?? '影片名稱'}}</label>
                                    <input type="text" class="form-control" name="title" id="title"
                                           placeholder="{{trans('default.video.title') ?? 'title'}}"
                                           value="{{$video->title}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.fan_id') ?? '番号'}}</label>
                                    <input type="text" class="form-control" name="fan_id" id="fan_id"
                                           placeholder="{{trans('default.video.fan_id') ?? 'title'}}"
                                           value="{{$video->fan_id}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.m3u8') ?? 'M3U8'}}</label>
                                    
                                    <input type="text" class="form-control" name="m3u8" id="m3u8"
                                           disabled="true" 
                                           placeholder="{{trans('default.video.m3u8') ?? 'm3u8'}}"
                                           value="{{$video->m3u8}}">
                                    <input type="hidden" name="m3u8" value="{{$video->m3u8}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.input_tags') ?? '標籤'}}</label>
                                    <input type="text" class="form-control" name="tags" id="tag"
                                           placeholder="{{trans('default.video.m3u8') ?? '標籤'}}"
                                           value="{{$video->tags}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.input_actors') ?? '演員'}}</label>
                                    <input type="text" class="form-control" name="actors" id="actor"
                                           placeholder="{{trans('default.video.actor') ?? '演員'}}"
                                           value="{{$video->actors}}">
                                </div>

                                  <div class="card card-danger">
                                    <div class="card-body">
                                      <div class="row">
                                        <div class="col-1">
                                            <label>定價:</label>
                                        </div>
                                        <div class="col-2">
                                          <input type="text" class="form-control" name="coins" id="coins"
                                                 placeholder="{{trans('default.video.coins') ?? 'coins'}}"
                                                 value="{{$video->coins}}">
                                        </div>
                                        <div class="col-1">
                                            <label>是否免費:</label>
                                        </div>
                                        <div class="col-2">
                                              <select class="form-control form-control-lg" name="is_free">
                                                  @foreach($const::IS_FREE as $key => $value)
                                                      <option value="{{$key}}" @if($key == $video->is_free) selected=true @endif >{{$value}}</option>
                                                  @endforeach
                                              </select>
                                        </div>
                                        <div class="col-1">
                                            <label for="exampleInputEmail1">{{trans('default.video.category') ?? '分類'}}</label>
                                        </div>
                                        <div class="col-5">
                                              <select class="form-control form-control-lg" name="category">
                                                  @foreach($const::CATEGORY as $key => $value)
                                                      <option value="{{$key}}" @if($key == $video->category) selected=true @endif >{{$value}}</option>
                                                  @endforeach
                                              </select>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                                <div class="form-group" style="display:flex;padding: 3px;">
                                    <div>
                                      <label for="exampleInputEmail1">{{trans('default.video.cover_thumb') ?? '封面圖'}}</label>
                                      <img src="{{ env('IMG_URL').$video->cover_thumb }}" class="img-fluid mb-2" style="width: 228px; height: 250px;display: block;"/>
                                    </div>

                                      @if($video->gif_thumb)
                                        <div>
                                          <label for="exampleInputEmail1">{{trans('default.video.gif_thumb') ?? '封面圖'}}</label>
                                             <img src="{{ env('IMG_URL').$video->gif_thumb }}" class="img-fluid mb-2" style="width: 228px; height: 250px;display: block;"/>
                                        </div>
                                      @endif  
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.release_time') ?? '上架時間'}}</label>
                                    <input type="text" class="form-control" name="release_time" placeholder="name" value="{{$video->release_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.video.pay_type') ?? '套圖描述'}}</label>
                                    <select name="pay_type" class="form-control">
                                        @foreach(trans('default.video.pay_type_types') as $key => $value)
                                            <option value="{{ $key }}" @if($key == ($model->pay_type ?? null)) @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">{{trans('default.video.hot_order') ?? '大家都在看排序'}}{{ trans('default.video.hot_order_desc') }}</label>
                                        <input type="text" class="form-control" name="hot_order" placeholder="hot_order" value="{{$video->hot_order ?? 0}}">
                                    </div>
                                <button type="submit"
                                        class="btn btn-primary">{{trans('default.submit') ?? '送出'}}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->


        </div>
        <!-- /.col -->
    </div>

    <script>
        $('input[name="release_time"]').daterangepicker({
            singleDatePicker: true,
            timePicker:true,
            timePicker24Hour: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY-M-DD HH:mm:00'
            }
        });
    </script>

@endsection
