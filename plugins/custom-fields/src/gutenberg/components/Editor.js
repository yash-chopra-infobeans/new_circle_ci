const { BlockEditorProvider, BlockList } = wp.blockEditor;
const { createBlock } = wp.blocks;
const { useState } = wp.element;
const { DropZoneProvider } = wp.components;

const Editor = ({ settings: _settings = {} }) => {
  const [blocks, updateBlocks] = useState([createBlock('cf/block', {})]);

  return (
    <DropZoneProvider>
      <BlockEditorProvider
        value={blocks}
        onInput={updateBlocks}
        onChange={updateBlocks}
        settings={_settings}
      >
        <BlockList />
      </BlockEditorProvider>
    </DropZoneProvider>
  );
};

export default Editor;
