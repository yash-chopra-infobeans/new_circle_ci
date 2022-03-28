<?php

/**
 * Extend WP Block Parser.
 */
class IDG_Block_Parser extends WP_Block_Parser {
	/**
	 * Extend add_inner_block and pass additional attribute to block
	 * to determine whether it is an inner block.
	 *
	 * @param WP_Block_Parser_Block $block        The block to add to the output.
	 * @param int                   $token_start  Byte offset into the document where the first token for the block starts.
	 * @param int                   $token_length Byte length of entire block from start of opening token to end of closing token.
	 * @param int|null              $last_offset  Last byte offset into document if continuing form earlier output.
	 */
	public function add_inner_block( WP_Block_Parser_Block $block, $token_start, $token_length, $last_offset = null ) {
		$block->attrs['isInnerBlock'] = true;
		return parent::add_inner_block( $block, $token_start, $token_length, $last_offset );
	}
}

add_filter(
	'block_parser_class',
	function() {
		return 'IDG_Block_Parser';
	}
);
