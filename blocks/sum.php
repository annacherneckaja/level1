<form action="" method="get" align="center">
    Enter the numbers: <br/>
    <div class="form-group">

        <div class="row justify-content-center align-items-center">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="col-1">
                    <label>
                        <input type="text" class="form-control" name="nums[]">
                    </label>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Sum</button>
</form>