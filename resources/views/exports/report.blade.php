<html>
<table class="table">
    <thead>
        <tr>
            <th rowspan="2">Category</th>
            @foreach ($month as $m)
                <th>{{ $year }}-{{ $m }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($month as $m)
                <th>Amount</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($credit as $key => $category)
            <tr>
                <td>{{$category['nama_kategori']}}</td>
                @foreach ($category['report'] as $item)
                    <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td>{{$total_income['nama']}}</td>
            @foreach ($total_income['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
            @endforeach
        </tr>
        @foreach ($debit as $key => $category)
            <tr>
                <td>{{$category['nama_kategori']}}</td>
                @foreach ($category['report'] as $item)
                    <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td>{{$total_expense['nama']}}</td>
            @foreach ($total_expense['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
            @endforeach
        </tr>
        <tr>
            <td>{{$net_income['nama']}}</td>
            @foreach ($net_income['total_all'] as $item)
                <td>{{currencyFormat($item['total'],'Rp. ',2)}}</td>
            @endforeach
        </tr>
    </tbody>
</table>
</html>