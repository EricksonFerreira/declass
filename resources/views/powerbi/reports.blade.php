<!DOCTYPE html>
<html>
<head>
    <title>Power BI Reports</title>
</head>
<body>
    <h1>Relatórios Power BI</h1>
    <table class="table table-condensed table-sm tabl-striped table-bordered">
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Nome</th>
                <th>Link</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports  as $report)
                <tr>
                    <td>{{ $report->group_name }}</td>
                    <td>{{ $report->name }}</td>
                    <td>
                        <a href="{{ route('powerbi.report', ['reportId' => $report->id]) }}">
                            Visualizar
                        </a>
                    </td>
                </tr>
            @endforeach

    </table>
</body>
</html>
