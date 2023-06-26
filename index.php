<?php

require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

$url = "https://fantasyleague-2bi2w.ondigitalocean.app/players-list?competitionFeed=FANL&seasonId=2024&pageRecords=1000&pageNumber=1";

$context = stream_context_create(array(
    'http' => array(
        'timeout' => 5  // Timeout in seconds
    )
));

$jsonData = file_get_contents($url, 0, $context);

if ($jsonData === false) {
    echo "Error fetching the data\n";
} else {
    $data = json_decode($jsonData, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "Error reading the JSON data\n";
    } else {
        $date = date("D, d M");
        $name = 'Player List - Fantasy League';

        $mpdf->defaultfooterfontsize = 8;
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->SetHeader(''.$date.'||'.$name.'');
        $mpdf->SetFooter('|{PAGENO}|');
        $mpdf->WriteHTML('<columns column-count="3" vAlign="J" column-gap="7" />');

        $positions = array_reduce($data['data'], function($carry, $item) {
            $carry[$item['positionId']][] = $item;
            return $carry;
        }, []);
        
        array_walk($positions, function(&$group) {
            usort($group, function($a, $b) {
                return strcmp($a['clubName'], $b['clubName']);
            });
        });
        
        $positionList = [
            1 => 'GOALKEEPERS',
            2 => 'CENTRE-BACKS',
            3 => 'MIDFIELDERS',
            4 => 'STRIKERS',
            6 => 'FULL-BACKS',
            7 => 'DEFENSIVE MIDFIELDERS',
        ];
        foreach ($positions as $positionKey => $position) {

            // if ($data->where('position', $positionKey)->count()) {
                $mpdf->WriteHTML('<table width="100%" border="0" style="font-size: 10px; font-family: sans-serif;">');
                $mpdf->WriteHTML('<thead><tr><td></td><td colspan="3" style="font-weight: bold;margin-bottom:5px;margin-top:5px;">'.$positionList[$positionKey].'</td></tr><tr><td style="font-weight: bold;">Code</td><td style="font-weight: bold;">Name</td><td style="font-weight: bold;">Club</td><td style="font-weight: bold;">Pts</td></tr></thead>');

                foreach ($position as $player) {
                    $mpdf->WriteHTML('<tr>');
                    $mpdf->WriteHTML('<td>'.$player['id'].'</td><td>'.$player['short'].'</td><td>'.$player['clubName'].'</td><td>'.$player['points'].'</td>');
                    $mpdf->WriteHTML('</tr>');
                }

                // $mpdf->WriteHTML('<tr>');
                // $mpdf->WriteHTML('<td colspan="4">&nbsp;</td>');
                // $mpdf->WriteHTML('</tr>');

                $mpdf->WriteHTML('</table>');
                // $i++;
            // }
        }

        // Save the PDF file:
        $mpdf->Output();
        //$mpdf->Output('myPdfFile.pdf', 'F');

        // if (! $forApi) {
        //     $mpdf->Output(storage_path($name.'.pdf'));
        //     $headers = ['Content-Type: application/pdf'];

        //     return Response::download(storage_path($name.'.pdf'), ''.$name.'.pdf', $headers)->deleteFileAfterSend(true);
        // } else {

        //     $printable = public_path('printable');
        //     $this->createFolder($printable);
        //     $mpdf->Output($printable.'/'.$name.'.pdf', 'F');

        //     return add_slash_in_url_end(config('app.url')).'printable/'.$name.'.pdf';
        // }
    }
}

?>