<h4 class="fw-bold">{{ $hospital->name }} - {{ $hospital->city }}</h4>
<hr>

<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>فصيلة الدم</th>
            <th>الوحدات المتاحة</th>
        </tr>
    </thead>
    <tbody>
        @php
            $types = ['O+','O-','A+','A-','B+','B-','AB+','AB-'];
            $map = array_fill_keys($types, 0);
            foreach ($hospital->bloodStock as $s) $map[$s->blood_type] = $s->units_available;
        @endphp

        @foreach($types as $type)
            <tr>
                <td><strong>{{ $type }}</strong></td>
                <td>{{ $map[$type] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
