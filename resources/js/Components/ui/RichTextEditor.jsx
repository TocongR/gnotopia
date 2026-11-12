import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';

function ToolbarButton({ label, active, disabled, onClick, children }) {
    return (
        <button
            type="button"
            aria-label={label}
            title={label}
            disabled={disabled}
            onClick={onClick}
            className={`rounded px-2 py-1 text-sm disabled:opacity-30 ${
                active ? 'bg-brand-800 text-white' : 'text-stone-700 hover:bg-stone-200'
            }`}
        >
            {children}
        </button>
    );
}

export default function RichTextEditor({ value, onChange }) {
    const editor = useEditor({
        extensions: [StarterKit],
        content: value || '',
        shouldRerenderOnTransaction: true,
        editorProps: {
            attributes: { class: 'rich-text min-h-[260px] px-4 py-2 focus:outline-none' },
        },
        onUpdate: ({ editor }) => onChange(editor.getHTML()),
    });

    if (!editor) {
        return null;
    }

    const chain = () => editor.chain().focus();

    return (
        <div className="mt-1 rounded-md border border-stone-300 bg-white shadow-sm">
            <div className="flex flex-wrap gap-1 border-b border-stone-200 bg-stone-50 p-2">
                <ToolbarButton label="Bold" active={editor.isActive('bold')} onClick={() => chain().toggleBold().run()}>
                    <strong>B</strong>
                </ToolbarButton>
                <ToolbarButton label="Italic" active={editor.isActive('italic')} onClick={() => chain().toggleItalic().run()}>
                    <em>I</em>
                </ToolbarButton>
                <ToolbarButton
                    label="Heading"
                    active={editor.isActive('heading', { level: 2 })}
                    onClick={() => chain().toggleHeading({ level: 2 }).run()}
                >
                    H2
                </ToolbarButton>
                <ToolbarButton
                    label="Subheading"
                    active={editor.isActive('heading', { level: 3 })}
                    onClick={() => chain().toggleHeading({ level: 3 }).run()}
                >
                    H3
                </ToolbarButton>
                <ToolbarButton label="Bullet list" active={editor.isActive('bulletList')} onClick={() => chain().toggleBulletList().run()}>
                    • List
                </ToolbarButton>
                <ToolbarButton label="Numbered list" active={editor.isActive('orderedList')} onClick={() => chain().toggleOrderedList().run()}>
                    1. List
                </ToolbarButton>
                <ToolbarButton label="Quote" active={editor.isActive('blockquote')} onClick={() => chain().toggleBlockquote().run()}>
                    “ ”
                </ToolbarButton>
                <ToolbarButton label="Code block" active={editor.isActive('codeBlock')} onClick={() => chain().toggleCodeBlock().run()}>
                    {'</>'}
                </ToolbarButton>
                <ToolbarButton label="Undo" disabled={!editor.can().undo()} onClick={() => chain().undo().run()}>
                    ↶
                </ToolbarButton>
                <ToolbarButton label="Redo" disabled={!editor.can().redo()} onClick={() => chain().redo().run()}>
                    ↷
                </ToolbarButton>
            </div>

            <EditorContent editor={editor} />
        </div>
    );
}