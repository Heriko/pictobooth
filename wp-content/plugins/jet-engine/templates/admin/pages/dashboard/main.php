<?php
/**
 * Main dashboard template
 */
?>
<div class="wrap">
	<h1 class="cs-vui-title"><?php _e( 'JetEngine dashboard', 'jet-engine' ); ?></h1>
	<div id="jet_engine_dashboard">
		<div class="cx-vui-panel">
			<cx-vui-tabs
				:in-panel="false"
				value="modules"
				layout="vertical"
			>
				<cx-vui-tabs-panel
					name="modules"
					label="<?php _e( 'Modules', 'jet-engine' ); ?>"
					key="modules"
				>
					<cx-vui-checkbox
						name="modules"
						label="<?php _e( 'Available Modules', 'jet-engine' ); ?>"
						description="<?php _e( 'Enable/disable additional JetEngine features', 'jet-engine' ); ?>"
						return-type="array"
						:wrapper-css="[ 'vertical-fullwidth' ]"
						:options-list="availableModules"
						v-model="activeModules"
					></cx-vui-checkbox>
					<cx-vui-component-wrapper
						:wrapper-css="[ 'vertical-fullwidth' ]"
					>
						<cx-vui-button
							button-style="accent"
							:loading="saving"
							@click="saveModules"
						>
							<span
								slot="label"
								v-html="'<?php _e( 'Save', 'jet-engine' ); ?>'"
							></span>
						</cx-vui-button>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<span
							class="cx-vui-inline-notice cx-vui-inline-notice--success"
							v-if="'success' === result"
							v-html="successMessage"
						></span>
						<span
							class="cx-vui-inline-notice cx-vui-inline-notice--error"
							v-if="'error' === result"
							v-html="errorMessage"
						></span>
					</cx-vui-component-wrapper>
				</cx-vui-tabs-panel>
				<cx-vui-tabs-panel
					name="skins"
					label="<?php _e( 'Skins Manager', 'jet-engine' ); ?>"
					key="skins"
				>
					<br>
					<div
						class="cx-vui-subtitle"
						v-html="'<?php _e( 'Skins manager', 'jet-engine' ); ?>'"
					></div>
					<div class="jet-engine-skins-wrap">
						<jet-engine-skin-import></jet-engine-skin-import>
						<jet-engine-skin-export></jet-engine-skin-export>
						<jet-engine-skins-presets></jet-engine-skins-presets>
					</div>
				</cx-vui-tabs-panel>
				<cx-vui-tabs-panel
					name="shortcode_generator"
					label="<?php _e( 'Shortcode Generator', 'jet-engine' ); ?>"
					key="shortcode_generator"
				>
					<div
						class="cx-vui-subtitle"
						v-html="'<?php _e( 'Generate shortcode', 'jet-engine' ); ?>'"
					></div>
					<div class="jet-shortocde-generator">
						<p><?php
							_e( 'Generate shortcode to output JetEngine-related data anywhere in content', 'jet-engine' );
						?></p>
						<cx-vui-select
							label="<?php _e( 'Component', 'jet-engine' ); ?>"
							description="<?php _e( 'Select plugin component to get value from', 'jet-engine' ); ?>"
							:options-list="componentsList"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							v-model="shortcode.component"
						></cx-vui-select>
						<cx-vui-input
							label="<?php _e( 'Meta Fields Name', 'jet-engine' ); ?>"
							description="<?php _e( 'Set meta field name to get value from', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							v-model="shortcode.meta_field"
							:conditions="[
								{
									input: this.shortcode.component,
									compare: 'equal',
									value: 'meta_field',
								}
							]"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Page Slug', 'jet-engine' ); ?>"
							description="<?php _e( 'Set created option page slug to get option from', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							v-model="shortcode.page"
							:conditions="[
								{
									input: this.shortcode.component,
									compare: 'equal',
									value: 'option',
								}
							]"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Field Name', 'jet-engine' ); ?>"
							description="<?php _e( 'Set option field name to get value from', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							v-model="shortcode.field"
							:conditions="[
								{
									input: this.shortcode.component,
									compare: 'equal',
									value: 'option',
								}
							]"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Post ID', 'jet-engine' ); ?>"
							description="<?php _e( 'Be default shortcodetries automatically detect post ID, use this option to set specific post ID', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							v-model="shortcode.post_id"
							:conditions="[
								{
									input: this.shortcode.component,
									compare: 'equal',
									value: 'meta_field',
								}
							]"
						></cx-vui-input>
						<div class="jet-shortocde-generator__result">
							[{{ generatedShortcode }}]
						</div>
					</div>
				</cx-vui-tabs-panel>
				<?php do_action( 'jet-engine/dashboard/tabs' ); ?>
			</cx-vui-tabs>
		</div>
	</div>
</div>