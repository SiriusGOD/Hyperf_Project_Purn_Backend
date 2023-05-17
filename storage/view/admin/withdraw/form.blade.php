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
                                 
                                    <input type="hidden" name="id" value="{{$data->id}}">
                                 
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.member') ?? '使用者 '}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                           value="{{$data->member->name}}">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.name') ?? '提款人'}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                            
                                           value="{{$data->name}}">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.bank_type') ?? '銀行類型'}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                        
                                           value="{{$BANK[$data->type]}}">
                                </div>

                                

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.date') ?? '日期'}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                           value="{{$data->payed_at}}">
                                </div>

                                 

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.account') ?? '銀行帳號'}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                           @if(!empty($data->account)) disabled="true" @endif
                                           
                                           value="{{$data->account}}">
                                </div>

                                

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.withdraw.status') ?? '銀行帳號'}}</label>
                                    <input type="text" class="form-control" name="account" id="account"
                                            disabled="true"  
                                           
                                           value="{{$STATUS[$data->status]}}">
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
