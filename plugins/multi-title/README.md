# Multi-Title
Multi-Title gives your site the ability to support different titles that can be customized by the users. Each title is neatly stored under its own tab; these tabs can be customized to match your publication — for example, Headline, Short Title & Frontpage Title. Each title and/or prefix can be stored in its own meta field rather than the single meta field used by default for all values. You have the option to enable a prefix (kicker) for your title on individual tabs, or a global standfirst. All of these fields support the possibility of character counting towards a limit with the addition of the option of preventing publishing if the limit is reached.

## Configuration Notes
The header block is powered by a JavaScript file that you define within a given project. In order to use this file, it must be enqueued within your WordPress plugin or theme, and included as part of a defined template.

**Example PHP usage below:**
``` php
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_script( 'some_unique_handle', 'uri/path/to/your/custom/js/file' );
});

// Anonymous callback functions are used here for brevity only – please be sure to include the body of these functions within a namespaced function or class method.

add_action( 'init', function() {
        $post_type_object           = get_post_type_object( 'post' );
        $post_type_object->template = [
            [ 'bigbite/multi-title' ],
            // Other block elements that are part of the template...
        ];
	}
);
```

**Example JS usage below:**
``` js
const BLOCKS_TEMPLATE = [
    [ 'bigbite/multi-title', {} ],
];

registerBlockType( 'myplugin/template', {
    title: 'My Template Block',
    category: 'widgets',
    edit: ( props ) => {
        return el( InnerBlocks, {
            template: BLOCKS_TEMPLATE,
            templateLock: false
        });
    },
    save: ( props ) => {
        return el( InnerBlocks.Content, {} );
    },
});
```

See the [`multi_title_tabs`](#multi_title_tabs) section for an example of how
your JavaScript file might be structured.

## Filters
### multi_title_tabs
Use `multi_title_tabs` to define your titles.

You can also access the current tabs by passing the tabs within the callback.

Example JS (defining titles):
``` js
addFilter('multi_title_tabs', {namespace}, () => ([
  {
    name: 'headline',
    title: 'Headline',
    className: 'tab tab-headline',
  }, {
    name: 'seo', // required
    title: 'SEO', // required
    className: 'tab tab-seo', // required
    isTitle: true, // by default the first tabs value will be kept in sync with the title of the article, add isTitle: true to the tab you want to be in sync with the title if the default is not what you want
    metaKey: 'multi_title_seo', // the name of the meta field the value should be saved to otherwise it'll be saved along with other field(s) in the single multi titles meta field.
    inheritValueFrom: 'headline', // setting a default will mean the seo field will initially take on the value of the headline field if seo is empty.
    combineCharLimit: true, // if the character limit should include the prefix value length so this example total characters would be 25
    charLimit: 10, // limit the field to X number of characters
    blockPublishOnError: true, // on error prevent post from being published (default: false)
    prefix: {
      enabled: true, //  display input for prefix
      charLimit: 15, // limit the prefix field to X number of characters
      inheritValueFrom: 'headline', // the prefix field will inherit the headline prefix if empty
      metaKey: 'multi_title_seo_prefix', // the name of the meta field the value should be saved to otherwise it'll be saved along with other field(s) in the single multi titles meta field.
    },
    additionalFields: {
      render: attrs => <AdditionalFields {...attrs} />,
      metaKeys: [ // it's only beneficial to define meta keys here if you're installing the plugin where you have existing data in individual meta fields so that when the block loads they can be set as block attributes.
        'multi_title_seokeywords',
        'multi_title_seodesc',
      ],
    }, // any custom fields you which too add example AdditionalFields component below
    onChange: ({ tab, name, value }) => { // do something on input change, like update the post slug for example
        const sluggedValue = getSluggedValue(title); // custom function 

        dispatch('core/editor').editPost({
          slug: sluggedValue,
        });

        return title
    },
  }, {
    name: 'subtitle',
    title: 'Subtitle',
    metaKey: 'multi_title_subtitle', // the name of the meta field the value should be saved to.
    inheritPlaceholderFrom: 'headline', // value of the headline field will be used as placeholder
    placeholder: 'custom placeholder', // default will be the title if not set
    className: 'tab tab-subtitle',
  },
]));
```

**Note: when setting `metaKey`, you must have registered the field in PHP.**

Example PHP (registering meta):
``` php
function register_multi_title_meta() {
  $args = array(
    'auth_callback' => 'is_user_logged_in',
    'type'          => 'string',
    'single'        => true,
    'show_in_rest'  => true,
  );

  register_meta( 'post', 'multi_title_seo', $args );
  register_meta( 'post', 'multi_title_seo_prefix', $args );
  register_meta( 'post', 'multi_title_subtitle', $args );
}

add_action( 'init', 'register_multi_title_meta' );
```

#### AdditionalFields:

**handleChange(tab: object, fieldName: string, meta: bool)** - Used to update multi-title block values (updates value in attributes and meta), meta by default is `false`, if you are saving the field's value in its own meta field, pass `true` (the corresponding registered meta key should equal the field name passed to `handleChange`).

``` js
const { WrappedInput } = window.MultiTitle;
const { Fragment } = wp.element;

const AdditionalFields = ({ handleChange, tab, getValue }) => (
  <Fragment>
    <WrappedInput
      rows="1"
      placeholder="Keywords"
      value={getValue(tab.name, 'multi_title_seokeywords')}
      className={`title-input title-input-${tab.name} title-input-sm`}
      onChange={handleChange(tab, 'multi_title_seokeywords', true)}
    />
    <WrappedInput
      rows="1"
      placeholder="Slug"
      value={getValue(tab.name, 'seoslug')}
      className={`title-input title-input-${tab.name} title-input-sm`}
      onChange={handleChange(tab, 'seoslug')}
      showCharLimitAt={0}
      showCharLimitOnFocus
      charLimit={50}
      charLimitValue={getValue(tab.name, 'seoslug')}
    />
    <WrappedInput
      rows="1"
      placeholder="Description"
      value={getValue(tab.name, 'multi_title_seodesc')}
      className={`title-input title-input-${tab.name} title-input-sm`}
      onChange={handleChange(tab, 'multi_title_seodesc', true)}
      showCharLimitAt={0}
      showCharLimitOnFocus
      charLimit={100}
      charLimitValue={getValue(tab.name, 'multi_title_seodesc')}
    />
  </Fragment>
);

export default AdditionalFields;
```

### multi_title_post_title
Define what the article title should be, for example you may want the article title to be what the user enters in the headline tab title field.

Example Usage:
``` js
addFilter('multi_title_post_title', 'the-times', (current, titles) => {
  const { headline = { value: '' } } = titles;
  return headline.value;
});
```

### multi_title_standfirst_enabled
Choose whether or not to display the standfirst field (disabled by default).

Example usage:
``` js
addFilter('multi_title_standfirst_enabled', {namespace}, () => true);
```
## Actions

## Slots
You can use slots to render components in areas of the block.

### multi-title-tab-{tabname}

Example usage:
``` js
const { registerPlugin } = wp.plugins;
const { Fill } = wp.components;
const { Fragment } = wp.element;

const RenderPlugin = () => (
  <Fragment>
    // other fills
    <Fill name="header-tab-headline">
      <p>some headline tab content</p>
    </Fill>
  </Fragment>
);

registerPlugin({plugin name}, {
  render: RenderPlugin,
});
```

### multi-title-below-tabs
Any children within a fill with the above name will render below the tabs but above the standfirst, if standfirst has been enabled.

Example usage:
``` js
const { registerPlugin } = wp.plugins;
const { Fill } = wp.components;
const { Fragment } = wp.element;

const RenderPlugin = () => (
  <Fragment>
    // other fills
    <Fill name="header-below-tabs">
      <p>below tabs content</p>
    </Fill>
  </Fragment>
);

registerPlugin({plugin name}, {
  render: RenderPlugin,
});
```

---

## Further Information
**Supported WordPress versions:** 5.2, 5.3, 5.4
**Maintained By:** Big Bite

