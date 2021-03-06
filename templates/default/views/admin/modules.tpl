<div class="row-fluid">
    <div class="span12 well">
        <div class="alert alert-info alert-block">
            <p>
                <span class="label label-info">Info!</span> Here you can specify which data should be logged.<br>
                Due to architectural limitations, you only can disable major types.
            </p>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <fieldset>
                    <h4>General modules</h4>

                    <label for="module_blocks" class="checkbox">
                        <input type="hidden" name="module_blocks" value="0"/>
                        <input type="checkbox" value="1" name="module_blocks" id="module_blocks"
                            {{ showChecked(1, module_blocks) }}>
                        Log Blocks
                        <small class="help-inline">This will log all block events like destroying or placing.</small>
                    </label>

                    <label for="module_items" class="checkbox">
                        <input type="hidden" name="module_items" value="0"/>
                        <input type="checkbox" value="1" name="module_items" id="module_items"
                            {{ showChecked(1, module_items) }}>
                        Log Items
                        <small class="help-inline">This will log all item events like dropping, using, picking up etc.</small>
                    </label>

                    <label for="module_deaths" class="checkbox">
                        <input type="hidden" name="module_deaths" value="0"/>
                        <input type="checkbox" value="1" name="module_deaths" id="module_deaths"
                            {{ showChecked(1, module_deaths) }}>
                        Log Deaths
                        <small class="help-inline">This will log all pvp, pve, evp and other death causes of the players.</small>
                    </label>

                    <label for="module_inventory" class="checkbox">
                        <input type="hidden" name="module_inventory" value="0"/>
                        <input type="checkbox" value="1" name="module_inventory" id="module_inventory"
                            {{ showChecked(1, module_inventory) }}>
                        Log and show players inventory
                        <small class="help-inline">This will log the inventory of every player.
                        </small>
                    </label>
                </fieldset>

                <fieldset>
                    <h4>Hooks</h4>

                    {% for hook in hooks %}
                        <label for="{{ hook.key }}" class="checkbox">
                            <input type="hidden" name="{{ hook.key }}" value="0"/>
                            <input type="checkbox" value="1" name="{{ hook.key }}" id="{{ hook.key }}"
                                    {{ showChecked(1, hook.value) }}>
                            Hook into {{ hook.key|slice(5)|replace({'.': '_'})|humanize }}
                        </label>
                    {% else %}
                        {{ 'No hooks in your database.'|trans }}
                    {% endfor %}
                </fieldset>
            </div>
        </div>
    </div>
</div>