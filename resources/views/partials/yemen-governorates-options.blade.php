@php
    $selected = $selected ?? '';
    $governorates = [
        'عدن',
        'أبين',
        'الضالع',
        'البيضاء',
        'الجوف',
        'الحديدة',
        'المهرة',
        'المحويت',
        'أمانة العاصمة',
        'عمران',
        'ذمار',
        'حضرموت',
        'حجة',
        'إب',
        'لحج',
        'مأرب',
        'ريمة',
        'صعدة',
        'صنعاء',
        'شبوة',
        'سقطرى',
        'تعز',
    ];
@endphp

@foreach ($governorates as $gov)
    <option value="{{ $gov }}" {{ $selected === $gov ? 'selected' : '' }}>{{ $gov }}</option>
@endforeach
