<section class="jumbotron text-center">
    <div class="container">
        <h1>Level 1</h1>
        <p class="lead text-muted">Anna Peredista</p>
        <?php $counter = file_get_contents("current_count.txt");
        //$counter = $counter == ""? 0: $counter+1;
        file_put_contents("current_count.txt", $counter+1);
        echo "page visited ".$counter." times"; ?>
        <br>
        <form method="post">
            <input type="submit" name="btReset"
                   class="btn btn-outline-primary" value="Reset counter" />

        </form>
        <?php
        if(isset($_POST['btReset'])) {
            file_put_contents("current_count.txt", 0);
            echo "<meta http-equiv='refresh' content='0'>";
        }
        ?>
    </div>
</section>