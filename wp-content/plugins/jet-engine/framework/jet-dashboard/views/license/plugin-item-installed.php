<div
	class="plugin-item plugin-item--installed"
	:class="{ 'update-avaliable': updateAvaliable }"
>
	<div
		class="plugin-item__inner"
		:class="{ 'proccesing-state': proccesingState }"
	>
		<div class="plugin-tumbnail">
			<img :src="pluginData.thumb">
		</div>
		<div class="plugin-info">
			<div class="plugin-name">
				<span class="plugin-label">{{ pluginData.name }}</span>
				<span class="plugin-version">{{ pluginData.currentVersion }}</span>
				<span
					class="plugin-rollback"
					v-if="versionRollbackAvaliable"
				>
					<cx-vui-button
						button-style="link-accent"
						size="link"
						@click="showRollbackPopup"
					>
						<span slot="label">
							<span>Change Version</span>
						</span>
					</cx-vui-button>
				</span>
			</div>
			<div
				class="plugin-update-label"
			>
				<div v-if="!updateAvaliable">Your plugin is up to date</div>
				<div v-if="updateAvaliable">
					Version <span class="latest-version">{{pluginData.version}}</span> available
					<cx-vui-button
						button-style="link-accent"
						size="link"
						:loading="updatePluginProcessed"
						@click="updatePlugin"
					>
						<span slot="label">
							<span>Update Now</span>
						</span>
					</cx-vui-button>
				</div>
			</div>
			<div class="plugin-actions">
				<cx-vui-button
					class="cx-vui-button--style-accent"
					button-style="default"
					size="mini"
					@click="showPopupActivation"
					v-if="activateLicenseVisible"
				>
					<span slot="label">
						<span>Activate License</span>
					</span>
				</cx-vui-button>
				<cx-vui-button
					class="cx-vui-button--style-danger"
					button-style="default"
					size="mini"
					:loading="licenseActionProcessed"
					@click="deactivateLicense"
					v-if="deactivateLicenseVisible"
				>
					<span slot="label">
						<span>Deactivate License</span>
					</span>
				</cx-vui-button>
				<cx-vui-button
					button-style="link-accent"
					size="link"
					:loading="actionPluginProcessed"
					v-if="activateAvaliable"
					@click="activatePlugin"
				>
					<span slot="label">
						<span>Activate Plugin</span>
					</span>
				</cx-vui-button>
				<cx-vui-button
					class="deactivate-plugin-button"
					button-style="link-accent"
					size="link"
					:loading="actionPluginProcessed"
					v-if="deactivateAvaliable"
					@click="deactivatePlugin"
				>
					<span slot="label">
						<span>Deactivate Plugin</span>
					</span>
				</cx-vui-button>
			</div>
		</div>
	</div>
	<transition name="popup">
		<cx-vui-popup
			class="rollback-popup"
			v-model="rollbackPopupVisible"
			:footer="false"
			body-width="450px"
		>
			<div class="cx-vui-popup__header-inner" slot="title">
				<div class="cx-vui-popup__header-label">{{ pluginData.name }} version rollback</div>
			</div>
			<div slot="content">
				<p><i>Warning: Please backup your database before making the rollback.</i></p>
				<cx-vui-select
					name="rollback-version"
					:wrapper-css="[ 'equalwidth' ]"
					size="fullwidth"
					:prevent-wrap="true"
					:options-list="rollbackOptions"
					v-model="rollbackVersion"
				>
				</cx-vui-select>
				<cx-vui-button
					button-style="accent"
					size="mini"
					v-if="rollbackButtonVisible"
					:loading="rollbackPluginProcessed"
					@click="rollbackPluginVersion"
				>
					<span slot="label">
						<span>Reinstall version {{ rollbackVersion }}</span>
					</span>
				</cx-vui-button>
			</div>
		</cx-vui-popup>
	</transition>
</div>

