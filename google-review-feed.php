<?php
 error_reporting(0);
//  if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
//         $url = "https://";   
// else  
//         $url = "http://";     
// $url.= $_SERVER['HTTP_HOST']; 
$url = 'https://design-heizungen.de';   
/**
 * 
 * Start::DB Connection configrations
 * 
 */
$servername =   "localhost";
$username   =   "xxx";
$password   =   "xxx";
$dbname     =   "xxx";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

/**
 * 
 * Start::DB Connection configrations
 * 
 */
$reviwes    =   [];
$sql        =   "
                    SELECT * FROM tbewertung
                ";
$result     =  $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $sql1        =   " SELECT *,(SELECT b.cWert FROM tartikelmerkmal pb,tmerkmalwertsprache b WHERE pb.kMerkmal= 11 AND b.kMerkmalWert=pb.kMerkmalWert AND pb.kArtikel=tartikel.kArtikel LIMIT 1 ) as brand_name FROM tartikel WHERE kVaterArtikel = {$row['kArtikel']} ORDER BY nSort ASC LIMIT 1 ";
    $result1     =   $conn->query($sql1);
    if($result1->num_rows == 0){
        $sql1        =   " SELECT *,(SELECT b.cWert FROM tartikelmerkmal pb,tmerkmalwertsprache b WHERE pb.kMerkmal= 11 AND b.kMerkmalWert=pb.kMerkmalWert AND pb.kArtikel=tartikel.kArtikel LIMIT 1 ) as brand_name FROM tartikel WHERE kArtikel = {$row['kArtikel']} ";
        $result1     =   $conn->query($sql1);
    }
    $product     =   $result1->fetch_assoc();
    $reviwes[]   =   [
                        'review_id'         =>  utf8_encode($row["kBewertung"]),
                        'reviewer'          =>  ['name'=>utf8_encode($row["cName"]),'reviewer_id'=>0],
                        'review_timestamp'  =>  utf8_encode($row["dDatum"]),
                        'title'             =>  utf8_encode($row["cTitel"]),
                        'content'           =>  utf8_encode($row["cText"]),
                        'review_url'        =>  utf8_encode($url.'/'.$product['cSeo']."#tab-votes"), 
                        'ratings'           =>  ['overall'=>utf8_encode($row['nSterne'])],
                        'products'          =>  [
                                                    'product'   =>  [
                                                        'product_ids' =>[
                                                                        'gtins'   =>  ['gtin'=>utf8_encode($product['cBarcode'])],
                                                                        'skus'    =>  ['sku'=>utf8_encode($product['cArtNr'])],
                                                                        'brands'  =>  ['brand'=>utf8_encode($product['brand_name'])],
                                                        ],
                                                        'product_name'  =>  utf8_encode($product['cName']),
                                                        'product_url'   =>  utf8_encode($url.'/'.$product['cSeo']."#tab-votes"),
                                                    ]
                                                ]
                    ];
                    //break;

  }
} else {
  echo "0 results";
}
$conn->close();
echo "<pre>";
$input_array = [
   'version'    => '2.3',
   'aggregator' =>  ['name'=>'Design Heizungen'],
   'publisher' =>   [
                        'name'      =>  'Design Heizungen',
                        'favicon'   =>  'https://design-heizungen.de/templates/novachild/themes/base/images/favicon.ico?v=1658693083'
                    ], 
   'reviews'   =>   [
                        'review'=>$reviwes
                    ] 
];

//print_r($input_array);

function makeDataXML($data) {
    $title  =  $data['publisher']['name'];
    //create the xml document
    $xmlDoc =  $doc = new DomDocument('1.0','UTF-8');
    
    $root   = $xmlDoc->appendChild($xmlDoc->createElement("feed"));
        $aggregator =   $root->appendChild($xmlDoc->createElement("aggregator"));
                        $aggregator->appendChild($xmlDoc->createElement('name', $data['aggregator']['name']));
        $publisher  =   $root->appendChild($xmlDoc->createElement("publisher"));
                        $publisher->appendChild($xmlDoc->createElement('name', $data['publisher']['name']));
                        $publisher->appendChild($xmlDoc->createElement('favicon', $data['publisher']['favicon']));
        $reviews    =   $root->appendChild($xmlDoc->createElement('reviews'));
        if(!empty($data['reviews']['review']))
        foreach($data['reviews']['review'] as $row){
            if(!empty($row)){
                $review     =   $reviews->appendChild($xmlDoc->createElement('review'));
                                $review->appendChild($xmlDoc->createElement('review_id',$row['review_id']));
                                $reviewer   =   $review->appendChild($xmlDoc->createElement('reviewer'));
                                                $reviewer->appendChild($xmlDoc->createElement('name',$row['reviewer']['name']));
                                                //$reviewer->appendChild($xmlDoc->createElement('reviewer_id',$row['reviewer']['reviewer_id']));
                                $review->appendChild($xmlDoc->createElement('review_timestamp',$row['review_timestamp']));
                                $review->appendChild($xmlDoc->createElement('title',$row['title']));
                                $review->appendChild($xmlDoc->createElement('content',$row['content']));
                                $review_url =   $review->appendChild($xmlDoc->createElement('review_url',$row['review_url'])); 
                                                $review_url->setAttribute('type','singleton');   
                                $ratings    =   $review->appendChild($xmlDoc->createElement('ratings'));
                                                $overall    =   $ratings->appendChild($xmlDoc->createElement('overall',$row['ratings']['overall']));
                                                $overall->setAttribute('min','1'); 
                                                $overall->setAttribute('max','5'); 
                                $products   =   $review->appendChild($xmlDoc->createElement('products')); 
                                                $product    =   $products->appendChild($xmlDoc->createElement('product'));
                                                                $product_ids    =   $product->appendChild($xmlDoc->createElement('product_ids'));
                                                                                    $gtins    =   $product_ids->appendChild($xmlDoc->createElement('gtins'));
                                                                                                $gtins->appendChild($xmlDoc->createElement('gtin',$row['products']['product']['product_ids']['gtins']['gtin']));
                                                                                    $skus     =   $product_ids->appendChild($xmlDoc->createElement('skus'));
                                                                                                $skus->appendChild($xmlDoc->createElement('sku',$row['products']['product']['product_ids']['skus']['sku'])); 
                                                                                    $brands   =   $product_ids->appendChild($xmlDoc->createElement('brands'));
                                                                                                $brands->appendChild($xmlDoc->createElement('brand',$row['products']['product']['product_ids']['brands']['brand']));             




                                                                $product->appendChild($xmlDoc->createElement('product_name',$row['products']['product']['product_name']));
                                                                $product->appendChild($xmlDoc->createElement('product_url',$row['products']['product']['product_url']));              
                
            }
        }
    $xmlDoc->saveXML();
    // $file_name = date('Y-m-d-H-i-s').time().'.xml';
    $file_name = 'google-product-review-feed-XhXalLjaprIUnghrB.xml';

    $xmlDoc->save($file_name);
    
    //return xml file name
    return $file_name;
}
echo makeDataXML($input_array);