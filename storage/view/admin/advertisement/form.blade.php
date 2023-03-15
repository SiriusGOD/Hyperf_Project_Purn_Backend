@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/advertisement/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="{{trans('default.ad_control.ad_input_name') ?? '請輸入廣告名稱'}}" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.image_profile_dec') ?? '圖片(不上傳就不更新，只接受圖片檔案(png jpeg gif))'}}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="customFile" accept="image/png, image/gif, image/jpeg">
                                        <label class="custom-file-label" for="customFile">{{trans('default.choose_file') ?? '選擇檔案'}}</label>
                                    </div>
                                </div>
                                <div class="form-group" id="modelImage">
                                    @if(!empty($model->image_url))
                                    <img src="{{$model->image_url}}" alt="image" style="width:100px">
                                    @endif
                                </div>
                                <div class="form-group" id="selectedFiles"></div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.ad_control.ad_connect_url') ?? '連結網址'}}</label>
                                    <input type="text" class="form-control" name="url" id="url" placeholder="{{trans('default.ad_control.ad_def_connect_url') ?? 'www.google.com'}}" value="{{$model->url ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.place') ?? '位置'}}</label>
                                    <select name="position" class="form-control form-control-lg">
                                        <option value="{{\App\Model\Advertisement::POSITION['top_banner']}}" {{($model->position ?? '') == \App\Model\Advertisement::POSITION['top_banner'] ? 'selected' : ''}}>
                                            {{trans('default.ad_control.ad_banner_up') ?? '上 banner'}}
                                        </option>
                                        <option value="{{\App\Model\Advertisement::POSITION['bottom_banner']}}" {{($model->position ?? '') == \App\Model\Advertisement::POSITION['bottom_banner'] ? 'selected' : ''}}>
                                            {{trans('default.ad_control.ad_banner_down') ?? '下 banner'}}
                                        </option>
                                        <option value="{{\App\Model\Advertisement::POSITION['popup_window']}}" {{($model->position ?? '') == \App\Model\Advertisement::POSITION['popup_window'] ? 'selected' : ''}}>
                                            {{trans('default.ad_control.ad_banner_pop') ?? '彈窗 banner'}}
                                        </option>
                                        <option value="{{\App\Model\Advertisement::POSITION['ad_image']}}" {{($model->position ?? '') == \App\Model\Advertisement::POSITION['ad_image'] ? 'selected' : ''}}>
                                            {{trans('default.ad_control.ad_image') ?? '圖片廣告'}}
                                        </option>
                                        <option value="{{\App\Model\Advertisement::POSITION['ad_link']}}" {{($model->position ?? '') == \App\Model\Advertisement::POSITION['ad_link'] ? 'selected' : ''}}>
                                            {{trans('default.ad_control.ad_link') ?? '友情鏈接'}}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.start_time') ?? '開始時間'}}</label>
                                    <input type="text" class="form-control" name="start_time" placeholder="name" value="{{$model->start_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.end_time') ?? '結束時間'}}</label>
                                    <input type="text" class="form-control" name="end_time" id="name" placeholder="name" value="{{$model->end_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.buyer') ?? '購買人'}}</label>
                                    <input type="text" class="form-control" name="buyer" id="name" placeholder="{{$buyer_msg ?? '請輸入廣告購買人名稱'}}" value="{{$model->buyer ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.take_up_down_info') ?? '上下架情況(任意時間均可下架，上架需在結束時間以前)'}}</label>
                                    <select class="form-control form-control-lg" name="expire">
                                        <option value="{{\App\Model\Advertisement::EXPIRE['no']}}" {{$model->expire == \App\Model\Advertisement::EXPIRE['no'] ? 'selected' : ''}}>
                                            {{trans('default.take_up') ?? '上架'}}
                                        </option>
                                        <option value="{{\App\Model\Advertisement::EXPIRE['yes']}}" {{$model->expire == \App\Model\Advertisement::EXPIRE['yes'] ? 'selected' : ''}}>
                                            {{trans('default.take_down') ?? '下架'}}
                                        </option>
                                    </select>
                                </div>
                                @if(!env('Single_Site'))
                                    @include("partial.admin.siteSelect")
                                @endif
                                <button type="submit" class="btn btn-primary">{{trans('default.submit') ?? '送出'}}</button>
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
        $(function() {
            $('input[name="start_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
            $('input[name="end_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
        });

        var selDiv = "";
        document.addEventListener("DOMContentLoaded", init, false);
        function init() {
            document.querySelector('#customFile').addEventListener('change', handleFileSelect, false);
            selDiv = document.querySelector("#selectedFiles");
        }
        function handleFileSelect(e) {
            var files = e.target.files;
            for(var i=0; i<files.length; i++) {
                var f = files[i];
                if(!f.type.match("image.*")) {
                    continue;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    var html = "<img src=\"" + e.target.result + "\" style='width:100px;'  >" ;
                    selDiv.innerHTML = html;
                }
                $('#modelImage').hide();
                reader.readAsDataURL(f);
            }
        }
    </script>

@endsection