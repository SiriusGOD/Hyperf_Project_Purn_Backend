@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/drive_group/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.tag_control.tag_name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('default.web_name_def') ?? '請輸入名稱'}}" value="{{$model->name ?? ''}}">
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
                                    <img src="{{\Hyperf\Support\env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live') . $model->image_url}}" alt="image" style="width:100px">
                                    @endif
                                </div>
                                <div class="form-group" id="selectedFiles"></div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.web_url') ?? '熱門標籤排序'}}</label>
                                    <input type="text" class="form-control" name="url" id="url" placeholder="{{ trans('default.web_url_def') ?? '請輸入網址'}}" value="{{$model->url ?? ''}}">
                                </div>
                                @include('partial.driveClassSelect')
                                <button type="submit" class="btn btn-primary">{{ trans('default.submit') ?? '送出'}}</button>
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