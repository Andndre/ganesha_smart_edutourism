{{-- Reusable TipTap script loader using ESM --}}
<script type="module">
    import { Editor } from 'https://esm.sh/@tiptap/core';
    import StarterKit from 'https://esm.sh/@tiptap/starter-kit';
    import Image from 'https://esm.sh/@tiptap/extension-image';

    // Expose library components to window
    window.TipTapEditor = Editor;
    window.TipTapStarterKit = StarterKit;
    window.TipTapImage = Image;

    // Helper to set Content in Tiptap
    window.setTiptapContent = function(textareaOrSelector, content) {
        const textarea = (typeof textareaOrSelector === 'string') 
            ? document.querySelector(textareaOrSelector) 
            : textareaOrSelector;
        if (textarea) {
            const container = textarea.closest('.tiptap-editor-container');
            if (container && container.editorInstance) {
                container.editorInstance.commands.setContent(content || '');
            } else {
                textarea.value = content || '';
            }
        }
    };

    // Helper to clear Tiptap Editors
    window.clearAllTiptapEditors = function(containerElement) {
        const parent = (typeof containerElement === 'string')
            ? document.querySelector(containerElement)
            : containerElement;
        if (parent) {
            parent.querySelectorAll('.tiptap-editor-container').forEach(container => {
                if (container.editorInstance) {
                    container.editorInstance.commands.setContent('');
                }
            });
        }
    };

    // Main Init Function
    window.initAllTiptapEditors = function() {
        document.querySelectorAll('.tiptap-editor-container').forEach(container => {
            if (container.dataset.initialized === 'true') return;

            const editorId = container.dataset.editorId;
            const toolbarId = container.dataset.toolbarId;
            const textareaId = container.dataset.textareaId;
            const hasImage = container.dataset.hasImage === 'true';
            const placeholder = container.dataset.placeholder || '';

            const editorEl = document.getElementById(editorId);
            const textarea = document.getElementById(textareaId);
            const toolbar = document.getElementById(toolbarId);

            if (!editorEl || !textarea) return;

            const extensions = [StarterKit];
            if (hasImage) {
                extensions.push(Image);
            }

            const editor = new Editor({
                element: editorEl,
                extensions: extensions,
                content: textarea.value || '',
                editorProps: {
                    attributes: {
                        class: 'focus:outline-none prose max-w-none text-sm text-gray-700 leading-relaxed min-h-20',
                    }
                },
                onUpdate({ editor }) {
                    textarea.value = editor.getHTML();
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    textarea.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            container.editorInstance = editor;

            // Connect Toolbar buttons
            if (toolbar) {
                function updateToolbarStates() {
                    const states = {
                        bold: editor.isActive('bold'),
                        italic: editor.isActive('italic'),
                        bulletList: editor.isActive('bulletList'),
                        orderedList: editor.isActive('orderedList')
                    };
                    
                    toolbar.querySelectorAll('button').forEach(btn => {
                        const action = btn.getAttribute('data-action');
                        if (states[action]) {
                            btn.classList.add('tiptap-btn-active');
                        } else {
                            btn.classList.remove('tiptap-btn-active');
                        }
                    });
                }

                toolbar.querySelectorAll('button').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const action = btn.getAttribute('data-action');

                        if (action === 'bold') editor.chain().focus().toggleBold().run();
                        else if (action === 'italic') editor.chain().focus().toggleItalic().run();
                        else if (action === 'bulletList') editor.chain().focus().toggleBulletList().run();
                        else if (action === 'orderedList') editor.chain().focus().toggleOrderedList().run();
                        else if (action === 'undo') editor.chain().focus().undo().run();
                        else if (action === 'redo') editor.chain().focus().redo().run();
                        else if (action === 'image') {
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/*';
                            input.onchange = async () => {
                                const file = input.files[0];
                                if (!file) return;

                                Swal.fire({
                                    title: 'Mengunggah Gambar...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                const formData = new FormData();
                                formData.append('image', file);
                                formData.append('_token', '{{ csrf_token() }}');

                                try {
                                    const response = await fetch('{{ route("admin.cultural-objects.upload-image") }}', {
                                        method: 'POST',
                                        body: formData
                                    });
                                    const data = await response.json();
                                    Swal.close();

                                    if (data.url) {
                                        editor.chain().focus().setImage({ src: data.url }).run();
                                    } else {
                                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengunggah gambar.', 'error');
                                    }
                                } catch (error) {
                                    Swal.close();
                                    Swal.fire('Gagal', 'Koneksi ke server terputus.', 'error');
                                }
                            };
                            input.click();
                        }
                        
                        updateToolbarStates();
                    });
                });

                editor.on('selectionUpdate', updateToolbarStates);
                editor.on('transaction', updateToolbarStates);
            }

            container.dataset.initialized = 'true';
        });
    };

    // Auto-init on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        window.initAllTiptapEditors();
    });
</script>
