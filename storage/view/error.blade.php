@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    @if(!empty($errors))
                        <div class="alert alert-danger">
                            <p><strong>欄位錯誤發生，5秒後回跳</strong></p>
                            <ul>
                                @foreach ($errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->


        </div>
        <!-- /.col -->
    </div>

    <script>
        $(function(){
            setTimeout(function(){
                history.back()
            }, 5000)
        })
    </script>
@endsection