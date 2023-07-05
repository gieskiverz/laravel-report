<html>

<head>
    <style>
        
        @import url('https://fonts.googleapis.com/css?family=Amatic+SC');
        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            background: linear-gradient(45deg, #49a09d, #5f2c82);
            font-family: sans-serif;
            font-weight: 100;
        }

        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        table {
            min-width: 1200px;
            width: 100%;
            max-width: 1920px; 
            border-collapse: collapse;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        th,
        td {
            padding: 15px;
            background-color: rgba(255,255,255,0.2);
            color: #fff;
        }

        th {
            text-align: left;
        }

        thead {
            th {
                background-color: #55608f;
            }
        }

        tbody {
            tr {
                &:hover {
                    background-color: rgba(255,255,255,0.3);
                }
            }
            td {
                position: relative;
                &:hover {
                    &:before {
                        content: "";
                        position: absolute;
                        left: 0;
                        right: 0;
                        top: -9999px;
                        bottom: -9999px;
                        background-color: rgba(255,255,255,0.2);
                        z-index: -1;
                    }
                }
            }
        }

        .button_container {
            position: absolute;
            left: 0;
            right: 0;
            top: 30%;
        }

        .btn {
            margin-top: 10px;
            border: none;
            display: block;
            text-align: center;
            cursor: pointer;
            text-transform: uppercase;
            outline: none;
            overflow: hidden;
            position: relative;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            background-color: #222;
            padding: 17px 60px;
            margin: 10 auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.20);
        }

        .btn span {
            position: relative;
            z-index: 1;
        }

        .btn:after {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 490%;
            width: 140%;
            background: #78c7d2;
            -webkit-transition: all .5s ease-in-out;
            transition: all .5s ease-in-out;
            -webkit-transform: translateX(-98%) translateY(-25%) rotate(45deg);
            transform: translateX(-98%) translateY(-25%) rotate(45deg);
        }

        .btn:hover:after {
            -webkit-transform: translateX(-9%) translateY(-25%) rotate(45deg);
            transform: translateX(-9%) translateY(-25%) rotate(45deg);
        }

        .link {
            font-size: 20px;
            margin-top: 30px;
        }

        .link a {
            color: #000;
            font-size: 25px;
        }
    </style>
</head>
<div class="container">
    <table>
        <thead>
            <tr style="background-color: #0491aa; color: white;">
                <th rowspan="2">Category</th>
                @foreach ($year as $m)
                <th>{{ $m }}</th>
                @endforeach
            </tr>
            <tr style="background-color: #0491aa; color: white;">
                @foreach ($year as $m)
                <th>Amount</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($result_income as $key => $category)
            <tr>
                <td>{{$category['nama_kategori']}}</td>
                @foreach ($category['report'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
            @endforeach
            <tr style="background-color: #aa0452; color: white;">
                <td>{{$total_income['nama']}}</td>
                @foreach ($total_income['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
            @foreach ($result_expense as $key => $category)
            <tr>
                <td>{{$category['nama_kategori']}}</td>
                @foreach ($category['report'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
            @endforeach
            <tr style="background-color: #bd7342; color: white;">
                <td>{{$total_expense['nama']}}</td>
                @foreach ($total_expense['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
            <tr style="background-color: #002050; color: white;">
                <td>{{$net_income['nama']}}</td>
                @foreach ($net_income['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    

<form id="exportForm" action="{{ route('export.report') }}" method="POST" target="_blank">
    @csrf
    <div class="modal-body">
        <div class="form-group mb-3">
        </div>
        <input type="hidden" name="range_filter" id="range_filter" value="{{$dateRange}}" />
        <input type="hidden" name="aksi" id="aksi" value="download" />
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn"><span>Download</span></button>
    </div>
</form>
</div>

</html>