@extends('backend.app')

@section('content')

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Settings</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>


        <section class="content">
            <div class="container-fluid">
                <!--<div class="row">
                    <div class="col-md-12">
                        <div class="card direct-chat direct-chat-warning collapsed-card">
                            <div class="card-header">
                                <h3 class="card-title">Add Site Setting</h3>

                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                            class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <br>
                                <center>
                                    <form method="post">
                                        <div class="form-group row col-md-11">
                                            <div class="col-md-3">
                                                <input type="text" name="setting_description" class="form-control"
                                                       required
                                                       placeholder="Setting name">
                                            </div>

                                            <div class="col-md-3">
                                                <input type="text" name="setting_key" class="form-control" required
                                                       placeholder="Keyword">
                                            </div>

                                            <div class="col-md-3">
                                                <select class="form-control select2" name="setting_type" id="">
                                                    <option value="textarea">Textarea</option>
                                                    <option value="img">Image</option>
                                                    <option value="input">Text</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <button id="settingsButton" type="button"
                                                        class="btn btn-primary btn-block"> Save
                                                </button>
                                            </div>

                                        </div>

                                    </form>
                                    <br>
                                </center>
                            </div>
                        </div>
                    </div>

                </div>-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Setting management</h3>
                    </div>
                    <div class="card-body">
                        <table class="example1 table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Setting Description</th>
                                <th>Keyword</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td>{{$setting->setting_description}}</td>
                                    <td>{{$setting->setting_key}}</td>
                                    <td>{{$setting->setting_value}}</td>
                                    <td>
                                        <a href="{{route('setting-edit',$setting->id)}}" class="btn btn-primary"><i
                                                class="fas fa-edit"></i>&nbsp;Edit</a>
                                        <button class="btn btn-danger "
                                                onclick="deleteSettings(this,'{{$setting->id}}')"><i
                                                class="fas fa-trash-alt"></i>&nbsp;Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('js')
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    <script src="{{asset('backend/plugins/sweetalert2/sweetalert2.min.js')}}"></script>
    <script src="{{asset('backend/plugins/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>


    <script>
        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            $("input[data-bootstrap-switch]").each(function () {
                $(this).bootstrapSwitch('state', $(this).prop('checked'));
            });
        });

    </script>

    //setting add
    <script>

        $("#settingsButton").click(function () {

            var url = "{{route("settings")}}";
            var form = new FormData($("form")[0]);

            $.ajax({
                type: "POST",
                url: url,
                data: form,
                processData: false,
                contentType: false,

                success: function (response) {
                    if (response.status == "success") {
                        toastr.success(response.content, response.title);
                    } else {
                        toastr.error(response.content, response.title);
                    }
                },
                error: function () {

                }
            });
        })
    </script>


    //setting delete
    <script>
        function deleteSettings(r, id) {
            var list = r.parentNode.parentNode.rowIndex;
            swal({
                title: 'Are you sure you want to delete it?',
                text: "It will not be recycled when you delete it!",
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Canel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes,Delete'
            }).then((result) => {
                if (result.value) {
                    $.ajax
                    ({
                        type: "Post",
                        url: '{{route('settings')}}',
                        data: {
                            'id': id,
                            'delete': 'delete'
                        },
                        beforeSubmit: function () {
                            swal({
                                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <span class="sr-only">Loading...</span>',
                                text: 'Siliniyor lütfen bekleyiniz...',
                                showConfirmButton: false
                            })
                        },
                        success: function (response) {
                            if (response.status == 'success') {
                                document.getElementById('example1').deleteRow(list);
                                toastr.success(response.content, response.title);
                            } else {
                                toastr.error(response.content, response.title);
                            }
                        }

                    })
                } else {
                }
            })
        }
    </script>

@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
@endsection
