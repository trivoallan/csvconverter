<div class="card">

  <h1><?php echo $id ? '#'.$id.' - ' : '' ?><?php echo $title ?></h1>

  <p class="metrics">
    <strong>Importance</strong> <span><?php echo $importance ? $importance : '&nbsp;' ?></span>
    <strong>Estimation initiale</strong> <span><?php echo $estimation ? $estimation : '&nbsp;' ?></span>
  </p>

  <p class="box">
    <?php echo nl2br($description) ?>
  </p>

</div>

<?php if ($rownum % 2 == 0): ?>
  <!-- <hr class="pagebreak" /> -->
<?php endif; ?>