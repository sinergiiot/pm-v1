<div>
    @if (isset($document))
        <livewire:document-versions-modal :document-id="$document->id" :key="'doc-versions-'.$document->id" />
    @endif
</div>
