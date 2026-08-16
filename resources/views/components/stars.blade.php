@props(['rating' => 0, 'size' => 13])

<span class="stars">
    @for ($i = 1; $i <= 5; $i++)
        <span @class(['star-filled' => $i <= $rating, 'star-empty' => $i > $rating])
              style="font-size:{{ $size }}px">★</span>
    @endfor
</span>
