import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { PanelBody, Button, ToolbarGroup, ToolbarButton, DropdownMenu, Dropdown } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { dispatch, select } from '@wordpress/data';
import { Fragment, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { PluginToolbar } from '@wordpress/edit-post';

const restBaseMap = {
    post: 'posts',
    page: 'pages',
    // Add custom post types and their rest_base if needed
};

function getRestBase(postType) {
    return restBaseMap[postType] || postType;
}

async function savePostAnd(callback) {
    await dispatch('core/editor').savePost();
    setTimeout(callback, 500); // Give time for save to complete
}

const getCurrentPostId = () => select('core/editor').getCurrentPostId();
const getCurrentPostType = () => select('core/editor').getCurrentPostType() || 'post';

const duplicatePost = async () => {
    const postId = getCurrentPostId();
    const postType = getCurrentPostType();
    const restBase = getRestBase(postType);
    try {
        // Fetch the current post data
        const post = await apiFetch({ path: `/wp/v2/${restBase}/${postId}` });
        // Prepare data for duplication
        const newPost = {
            ...post,
            status: 'draft',
            title: post.title.rendered + ' (copy)',
            slug: '',
        };
        // Remove fields that shouldn't be sent
        delete newPost.id;
        delete newPost.date;
        delete newPost.date_gmt;
        delete newPost.modified;
        delete newPost.modified_gmt;
        delete newPost.guid;
        // Create the duplicate
        const created = await apiFetch({
            path: `/wp/v2/${restBase}`,
            method: 'POST',
            data: newPost,
        });
        // Redirect to the new post's edit screen
        window.location = `/wp-admin/post.php?post=${created.id}&action=edit`;
    } catch (err) {
        alert(__('Failed to duplicate post: ', 'improved-save-button') + err.message);
    }
};

const goToNextOrPrevious = async (direction) => {
    const postId = getCurrentPostId();
    const postType = getCurrentPostType();
    const restBase = getRestBase(postType);
    try {
        // Get all posts of this type, ordered by date
        const posts = await apiFetch({
            path: `/wp/v2/${restBase}?per_page=100&orderby=date&order=asc&_fields=id,date`,
        });
        const idx = posts.findIndex((p) => p.id === postId);
        let targetIdx = direction === 'next' ? idx + 1 : idx - 1;
        if (targetIdx >= 0 && targetIdx < posts.length) {
            const targetId = posts[targetIdx].id;
            window.location = `/wp-admin/post.php?post=${targetId}&action=edit`;
        } else {
            alert(__('No ' + direction + ' post found.', 'improved-save-button'));
        }
    } catch (err) {
        alert(__('Failed to find ' + direction + ' post: ', 'improved-save-button') + err.message);
    }
};

const actions = [
    {
        id: 'fastwebcreations.new',
        label: __('Save and New', 'improved-save-button'),
        onClick: () => savePostAnd(() => {
            const postType = getCurrentPostType();
            const isDefault = !postType || postType === 'post';
            window.location = `/wp-admin/post-new.php${isDefault ? '' : '?post_type=' + postType}`;
        }),
    },
    {
        id: 'fastwebcreations.duplicate',
        label: __('Save and Duplicate', 'improved-save-button'),
        onClick: () => savePostAnd(duplicatePost),
    },
    {
        id: 'fastwebcreations.list',
        label: __('Save and List', 'improved-save-button'),
        onClick: () => savePostAnd(() => {
            const postType = getCurrentPostType();
            window.location = `${window.location.origin}/wp-admin/edit.php?post_type=${postType}`;
        }),
    },
    {
        id: 'fastwebcreations.return',
        label: __('Save and Return', 'improved-save-button'),
        onClick: () => savePostAnd(() => {
            if (document.referrer) {
                window.location = document.referrer;
            } else {
                alert(__('No previous page found.', 'improved-save-button'));
            }
        }),
    },
    {
        id: 'fastwebcreations.next',
        label: __('Save and Next', 'improved-save-button'),
        onClick: () => savePostAnd(() => goToNextOrPrevious('next')),
    },
    {
        id: 'fastwebcreations.previous',
        label: __('Save and Previous', 'improved-save-button'),
        onClick: () => savePostAnd(() => goToNextOrPrevious('previous')),
    },
    {
        id: 'fastwebcreations.view',
        label: __('Save and View', 'improved-save-button'),
        onClick: () => savePostAnd(() => {
            const permalink = select('core/editor').getPermalink();
            if (permalink) {
                window.open(permalink, '_self');
            } else {
                alert(__('Could not determine post URL.', 'improved-save-button'));
            }
        }),
    },
    {
        id: 'fastwebcreations.viewPopup',
        label: __('Save and View (Popup)', 'improved-save-button'),
        onClick: () => savePostAnd(() => {
            const permalink = select('core/editor').getPermalink();
            if (permalink) {
                window.open(permalink, '_blank');
            } else {
                alert(__('Could not determine post URL.', 'improved-save-button'));
            }
        }),
    },
];

// Get enabled action IDs from backend config
const enabledActionIds = (window.FastWebCreations?.SaveAndThen?.config?.actions || [])
    .filter(a => a.enabled)
    .map(a => a.id);

const filteredActions = actions.filter(action => enabledActionIds.includes(action.id));

const SaveAndThenPanel = () => (
    <PanelBody title={__('Save and Then Actions', 'improved-save-button')} initialOpen={true}>
        {filteredActions.map((action) => (
            <Button
                key={action.id}
                isPrimary
                style={{ display: 'block', marginBottom: '10px', width: '100%' }}
                onClick={action.onClick}
            >
                {action.label}
            </Button>
        ))}
        <p style={{ marginTop: '1em', color: '#888', fontSize: '0.9em' }}>
            {__('All actions are now enabled for Gutenberg. Some actions may require REST API permissions.', 'improved-save-button')}
        </p>
    </PanelBody>
);

const SaveAndThenDropdownButton = () => (
    <Dropdown
        popoverProps={{ placement: 'bottom-end' }}
        renderToggle={({ isOpen, onToggle }) => (
            <Button
                isPrimary
                onClick={onToggle}
                aria-expanded={isOpen}
                className="editor-post-publish-button editor-post-publish-button__button is-primary is-compact"
                style={{ display: 'flex', alignItems: 'center', gap: '8px' }}
            >
                {__('Save and Then', 'improved-save-button')}
                <span style={{ fontSize: '1.1em', marginLeft: '6px', lineHeight: 1 }} aria-hidden="true">▼</span>
            </Button>
        )}
        renderContent={() => (
            <div style={{ minWidth: 220, padding: 0 }}>
                {filteredActions.map((action) => (
                    <Button
                        key={action.id}
                        isPrimary
                        style={{
                            display: 'block',
                            width: '100%',
                            margin: 0,
                            borderRadius: 0,
                            borderBottom: '1px solid #e0e0e0',
                        }}
                        onClick={action.onClick}
                    >
                        {action.label}
                    </Button>
                ))}
            </div>
        )}
    />
);

const SaveAndThenReplaceSaveButton = () => {
    useEffect(() => {
        let observer;
        function injectButton() {
            const saveButton = document.querySelector(
                'button.editor-post-publish-button__button, button.editor-post-save-draft'
            );
            if (saveButton && !document.querySelector('.fwc-sat-save-and-then-replace-btn')) {
                saveButton.style.display = 'none';
                const mount = document.createElement('div');
                mount.className = 'fwc-sat-save-and-then-replace-btn';
                saveButton.parentNode.insertBefore(mount, saveButton);
                import('@wordpress/element').then(({ render, createElement }) => {
                    render(createElement(SaveAndThenDropdownButton), mount);
                });
            }
        }
        injectButton();
        observer = new MutationObserver(injectButton);
        observer.observe(document.body, { childList: true, subtree: true });
        return () => observer && observer.disconnect();
    }, []);
    return null;
};

registerPlugin('fwc-sat-save-and-then', {
    render: () => <SaveAndThenReplaceSaveButton />,
}); 