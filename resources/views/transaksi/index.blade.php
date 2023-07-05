@extends('adminlte::page')

@section('title', 'Transaksi')

@section('content_header')

<meta name="csrf-token" content="{{ csrf_token() }}">
    <h1>Transaksi</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <x-adminlte-button label="Add" theme="primary" icon="fas fa-plus" class="mb-3 mr-1 float-right" data-toggle="modal" data-target="#addTransaksiModal" value="add" id="addData"/>
        <x-adminlte-button label="Download Report" theme="success" icon="fas fa-file" class="mb-3 mr-1 float-right" data-toggle="modal" data-target="#exportModal" value="Export" id="exportData"/>
        {{-- <a href="{{ url('/export') }}" class="btn btn-success mb-3 mr-1 float-right">Download Excel</a> --}}
    </div>
</div>
<div id="notif"></div>

<table id="transaksiTable" class="table">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>COA Kode</th>
            <th>COA Nama</th>
            <th>Desc</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

<div class="modal fade" id="addTransaksiModal" tabindex="-1" aria-labelledby="addTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransaksiModalLabel">Add Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form id="addTransaksiForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Tanggal</label>
                            <div class="input-group date" id="tanggalpicker" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#tanggalpicker" name="tanggal" id="tanggal" required>
                                <div class="input-group-append" data-target="#tanggalpicker" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>COA</label>
                        <select class="custom-select" name="coa_id" id="coa_id">)
                            @foreach ($kategori as $key =>$value)
                                <optgroup label="{{$key}}">
                                @foreach ($value as $item)
                                    <option value="{{$item->id}}">{{$item->kode}} | {{$item->nama_coa}}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="desc" class="form-label">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Enter Desc ..." name="desc" id="desc"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="debit" class="form-label">Debit</label>
                        <input type="text" class="form-control" id="debit" name="debit" value=0 />
                    </div>
                    <div class="form-group mb-3">
                        <label for="credit" class="form-label">Credit</label>
                        <input type="text" class="form-control" id="credit" name="credit" value=0 />
                    </div>
                    <input type="hidden" name="action" id="action" value="Add" />
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                    <button type="submit" class="tambah btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
    <form method="post" id="sample_form" class="form-horizontal">
        <div class="modal-header">
            <h5 class="modal-title" id="ModalLabel">Confirmation</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
            <h5 align="center" style="margin:0;">Are you sure you want to remove this data?</h5>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
        </div>
    </form>  
    </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Download Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form id="exportForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Pilih Tahun</label>
                        <select class="custom-select" name="year" id="year">
                            @for ($i = 2000; $i <= $year; $i++)
                                @if ($i==$year)
                                    <option value="{{$i}}" selected>{{$i}}</option>
                                @else
                                    <option value="{{$i}}">{{$i}}</option>
                                @endif
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                    <button type="submit" class="btn btn-primary">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)
@section('plugins.TempusDominusBs4', true)
@section('plugins.JqueryValidation', true)
@section('js')
<script>
    $(function() {
        var table = $('#transaksiTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('transaksi.index') }}",
            columns: [
                {
                    data: 'iteration',
                    name: 'iteration',
                    orderable: false,
                    searchable: false
                },
                {data: 'tanggal', name: 'tanggal'},
                {data: 'kategori', name: 'kategori'},
                {data: 'coa_kode', name: 'coa_kode'},
                {data: 'coa_nama', name: 'coa_nama'},
                {data: 'desc', name: 'desc'},
                {data: 'debit', name: 'debit'},
                {data: 'credit', name: 'credit'},
                {data: 'created_at', name: 'created_at'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('#addTransaksiForm').validate({
            rules: {
                tanggal: {
                    required: true,
                    date: true
                },
                coa_id: {
                    required: true,
                    number: true
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $('#tanggalpicker').datetimepicker({
            format: 'L'
        });

        $('#addData').click(function(){
            $('input').removeClass('is-invalid');
            $('#debit').val("0");
            $('#credit').val("0");
            $('#action').val('Add'); 
            $('.tambah').html("Add");
            $('#addTransaksiForm')[0].reset();
        });
        // Handle form submission for adding transaksi
        $('#addTransaksiForm').submit(function(e) {
            e.preventDefault();
            var action_url = '';
            var html = '';
            var id = '';
            
            if($('#action').val() == 'Add')
            {
                action_url = "{{ route('transaksi.store') }}";
            }
    
            if($('#action').val() == 'Edit')
            {
                action_url = "{{ route('transaksi.update') }}";
            }

            $.ajax({
                url: action_url,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $('#addTransaksiModal').modal('hide');
                    if (response.success) {
                        html = '<div class="alert alert-success alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += response.message + '</div>'
                    } else {
                        html = '<div class="alert alert-danger alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += 'Failed</div>'
                    }
                    $('#addTransaksiForm')[0].reset();
                    $('#notif').html(html);
                    table.draw();
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                }
            });
        });

        
        $('#exportForm').submit(function(e) {
            e.preventDefault();
            window.open("export/report/"+$('#year').val(),'newStuff');
        });

        $(document).on('click', '.edit', function(event){
            event.preventDefault(); 
            $('#addTransaksiModal').modal('show');
            var id = $(this).attr('id');
    
            $.ajax({
                url :"/transaksi/edit/"+id,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType:"json",
                success:function(data)
                {
                    console.log(data.result);
                    $('#tanggal').val(data.result.tanggal);
                    $('#coa_id').val(data.result.coa_id).change();
                    $('#desc').val(data.result.desc);
                    $('#debit').val(data.result.debit);
                    $('#credit').val(data.result.credit);
                    $('#hidden_id').val(data.result.id);
                    $('#action').val('Edit'); 
                    $('.tambah').html("Save");
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                }
            })
        });

        
        $('#addTransaksiModal').on('click', '#close, .close', function(){
            $('input').removeClass('is-invalid');
        });

        var transaksi_id;
 
        $(document).on('click', '.hapus', function(){
            transaksi_id = $(this).attr('id');
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function(){
            $.ajax({
                url:"transaksi/destroy/"+transaksi_id,
                beforeSend:function(){
                    $('#ok_button').text('Deleting...');
                },
                success:function(response)
                {
                    if (response.success) {
                        html = '<div class="alert alert-success alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += response.message + '</div>'
                    } else {
                        html = '<div class="alert alert-danger alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += 'Failed</div>'
                    }
                    setTimeout(function(){
                    $('#ok_button').text('Ok');
                    $('#confirmModal').modal('hide');
                    $('#notif').html(html);
                    table.draw();
                    }, 500);
                }
            })
        });
    });
</script>
@stop