<?php



      
$dbhost = 'localhost';
$dbname = 'wpmovies';
$dbusername = 'root';
$dbpassword = '';
$postType = 'movie';
$commentStatus = 'closed';
// Create connection
$conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 





$url = "http://www.rcksld.com/GetNowShowing.json";


$jsonData = json_decode(file_get_contents($url));


 




foreach ($jsonData as  $data) {
    # code...

    //print_r($data[$loop]);
    for ($i=0; $i < count($data); $i++) { 
        # code...
       // if(print_r(count($data)) == 21){
        // echo "<pre>";
        $post_title = $data[$i]->Name;
        $post_content = $data[$i]->Synopsis;
        $post_excerpt = $data[$i]->PosterUrl;
        $comment_status = $commentStatus;
        $post_name = $data[$i]->Name;
        $guid = $data[$i]->PosterUrl;
       
        // print_r($data[$i]->LargePosterUrl);
        // echo "</pre>";
       
        $sql = "INSERT INTO wp_posts (post_title,
                                        post_content,
                                        post_excerpt,
                                        to_ping,
                                        pinged,
                                        post_content_filtered,
                                        post_type,
                                        comment_status,
                                        post_name,
                                        guid)
        VALUES ('".$post_title."',
                '".$post_content."',
                '".$post_excerpt."',
                '".$to_ping."',
                '".$pinged."',
                '".$post_content_filtered."',
                '".$postType."',
                '".$commentStatus."',
                '".$post_name."',
                '".$guid."')";
              
             
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
      
          
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
   

      //  }
       


       	


    }


}


 

$conn->close();
