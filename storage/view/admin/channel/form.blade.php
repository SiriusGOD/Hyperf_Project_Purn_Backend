@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="" method="post" class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.channel.name') ?? '使用者統計'}}</label>
                                    <input type="text" class="form-control" name="account" id="account" value="{{$data->member->name}}">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.channel.name') ?? '收益'}}</label>
                                    <input type="text" class="form-control" name="account" id="account" value="{{$data->name}}">
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

@endsection
