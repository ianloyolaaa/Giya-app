{{--
    Line chart, drawn as inline SVG.

    Replaces a client-side charting library so the dashboard renders with no
    JavaScript and no network. Values are plotted server-side from the data
    the controller already aggregated.

    @param  array  $labels   x-axis labels
    @param  array  $data     numeric series, same length as $labels
--}}
@props(['labels' => [], 'data' => []])

@php
    $W = 520; $H = 220;
    $padL = 34; $padR = 12; $padT = 12; $padB = 26;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;

    $count = max(count($data), 1);
    $max   = max(max($data ?: [0]), 1);
    // Round the axis ceiling up to a friendly number.
    $step  = (int) max(1, ceil($max / 4));
    $top   = $step * 4;

    $x = fn ($i) => $count > 1
        ? round($padL + ($i / ($count - 1)) * $plotW, 2)
        : round($padL + $plotW / 2, 2);
    $y = fn ($v) => round($padT + (1 - ($v / $top)) * $plotH, 2);

    $points = collect($data)->map(fn ($v, $i) => $x($i) . ',' . $y($v))->implode(' ');
    $area   = $points
        ? $x(0) . ',' . ($padT + $plotH) . ' ' . $points . ' ' . $x($count - 1) . ',' . ($padT + $plotH)
        : '';
@endphp

<svg class="chart-svg" viewBox="0 0 {{ $W }} {{ $H }}" role="img"
     aria-label="Line chart of monthly visits">

    @for ($g = 0; $g <= 4; $g++)
        @php $gy = $padT + ($g / 4) * $plotH; @endphp
        <line class="chart-grid-line" x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $W - $padR }}" y2="{{ $gy }}"/>
        <text class="chart-axis-text" x="{{ $padL - 8 }}" y="{{ $gy + 3 }}" text-anchor="end">
            {{ $top - $g * $step }}
        </text>
    @endfor

    @if ($area)
        <polygon class="chart-area" points="{{ $area }}"/>
        <polyline class="chart-line" points="{{ $points }}"/>
    @endif

    @foreach ($data as $i => $value)
        <circle class="chart-dot" cx="{{ $x($i) }}" cy="{{ $y($value) }}" r="4">
            <title>{{ $labels[$i] ?? '' }}: {{ $value }}</title>
        </circle>
        <text class="chart-axis-text" x="{{ $x($i) }}" y="{{ $H - 8 }}" text-anchor="middle">
            {{ $labels[$i] ?? '' }}
        </text>
    @endforeach
</svg>
