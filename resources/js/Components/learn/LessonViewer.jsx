export default function LessonViewer({ lesson }) {
    if (lesson.kind === 'video') {
        if (!lesson.embed_url) {
            return <p className="rounded-md bg-stone-100 p-4 text-sm text-stone-600">This video is not available.</p>;
        }

        return (
            <div className="aspect-video overflow-hidden rounded-md border border-stone-200 bg-stone-900">
                <iframe
                    src={lesson.embed_url}
                    title={lesson.title}
                    className="h-full w-full"
                    loading="lazy"
                    allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"
                    referrerPolicy="strict-origin-when-cross-origin"
                    allowFullScreen
                />
            </div>
        );
    }

    // The server sanitizes this HTML before it gets here.
    return <div className="rich-text text-stone-800" dangerouslySetInnerHTML={{ __html: lesson.body ?? '' }} />;
}