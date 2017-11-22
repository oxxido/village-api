@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <p>PDF Generation</p>
    @foreach ($data as $record)
    <pre> <?php print_r( $record );?></pre>
    @endforeach
@endsection

<?php /*

example structure
 Array
(
    [checkins] => Array
        (
            [0] => Array
                (
                    [id] => rec1Zk04FVVZfQAMm
                    [customer] => recPzqnKqO6Hf4zo7
                    [name] => David Brühlmeier (VO)
                    [email] => david.bruehlmeier@villageoffice.ch
                    [phone] => +41 79 500 09 92
                    [organization] => VillageOffice Genossenschaft
                    [space] => Effinger Coworking Space
                    [checkin] => 2017-10-03 | David Brühlmeier (VO) | Effinger Coworking Space
                    [timestamp] => 2017-10-03T05:22:00.000Z
                    [date] => 2017-10-03
                    [weekday] => Tuesday
                    [time] => 05:22
                )

            [1] => Array
                (
                    [id] => recp6iLyD3aK5EWAd
                    [customer] => recPzqnKqO6Hf4zo7
                    [name] => David Brühlmeier (VO)
                    [email] => david.bruehlmeier@villageoffice.ch
                    [phone] => +41 79 500 09 92
                    [organization] => VillageOffice Genossenschaft
                    [space] => Effinger Coworking Space
                    [checkin] => 2017-10-27 | David Brühlmeier (VO) | Effinger Coworking Space
                    [timestamp] => 2017-10-27T18:08:00.000Z
                    [date] => 2017-10-27
                    [weekday] => Friday
                    [time] => 18:08
                )

        )

    [name] => David Brühlmeier (VO)
    [email] => david.bruehlmeier@villageoffice.ch
    [idx] => david.bruehlmeier@villageoffice.ch
    [CHF] => 30.5
)
*/