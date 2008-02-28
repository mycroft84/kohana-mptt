<?php echo $open; ?>
	<fieldset>	
		<ol>
		<?php if ($legend != ''): 
			?><legend><?php echo $legend; ?></legend>
		<?php endif;
		
		foreach($inputs as $input):
		
			if ($input instanceof Element_Group):
				?>	<li>
			<?= $input->render();
				?>	</li>
				<?php
			else:
				if($input instanceof Element_Hidden):
					echo $input->render();
					
				else: 
					if ($label = $input->label()): 
						?>	<li><?php echo $input->label(); 
					endif; 
						echo $input->render(); 
		
					if($input->error_message() !== false):
			
						?><label class="error" for="<?=$input->name?>"><?php echo $input->error_message() ?></label><?php
					endif; ?></li>
		<?php 
				endif;
			endif;
		
		endforeach; ?>
		</ol>
	</fieldset>
<?php echo $close ?>