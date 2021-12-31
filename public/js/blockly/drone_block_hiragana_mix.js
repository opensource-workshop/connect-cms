Blockly.Blocks.drone_takeoff = {
  /**
   * Block for TakeOff.
   * @this Blockly.Block
   */
  init() {
    // ブロックの色を鮮やかにする。最初に設定することで、この後のブロックにも適用される。
    Blockly.HSV_SATURATION = 1;
    Blockly.HSV_VALUE = 0.5;

    // TakeOff の設定
    this.jsonInit({
		"type": "drone_takeoff",
		"message0": "離陸[とぶ]",
		"inputsInline": true,
		"previousStatement": null,
		"nextStatement": null,
		"colour": 315,
		"tooltip": "離陸[とぶ]",
		"helpUrl": ""
    });
  },
};

Blockly.PHP.drone_takeoff = function(block) {
  const args0 = Blockly.PHP.valueToCode(block, 'TEXT', Blockly.PHP.ORDER_FUNCTION_CALL) || '\'\'';
  const OPERATOR = "$tello->takeoff();";
  return [OPERATOR + args0, Blockly.PHP.ORDER_MEMBER];
};

Blockly.Blocks.drone_land = {
  /**
   * Block for Land.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
        "type": "drone_land",
        "message0": "着陸[おりる]",
        "inputsInline": false,
        "previousStatement": null,
        "nextStatement": null,
        "colour": 315,
        "tooltip": "着陸[おりる]",
        "helpUrl": ""
    });
  },
};

Blockly.Blocks.drone_up = {
  /**
   * Block for Up.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_up",
	  "message0": "上昇[うえ] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 230,
	  "tooltip": "上昇[うえ]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_down = {
  /**
   * Block for Down.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_down",
	  "message0": "下降[した] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 230,
	  "tooltip": "下降[した]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_forward = {
  /**
   * Block for Forward.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_forward",
	  "message0": "前進[まえ] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "前進[まえ]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_backward = {
  /**
   * Block for Backward
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_backward",
	  "message0": "後進[うしろ] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "後進[うしろ]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_left = {
  /**
   * Block for Left.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_left",
	  "message0": "左[ひだり] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "左[ひだり]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_right = {
  /**
   * Block for Right
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_right",
	  "message0": "右[みぎ] (cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "右[みぎ]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_leftturn = {
  /**
   * Block for Left turn.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_leftturn",
	  "message0": "左回転[ひだりまわり] (角度) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 90,
	  "tooltip": "左回転[ひだりまわり]",
	  "helpUrl": ""
	});
  },
};

Blockly.Blocks.drone_rightturn = {
  /**
   * Block for Right turn.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_rightturn",
	  "message0": "右回転[みぎまわり] (角度) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 0
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 90,
	  "tooltip": "右回転[みぎまわり]",
	  "helpUrl": "",
	});
  },
};

Blockly.Blocks.drone_flip = {
  /**
   * Block for Flip.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
	  "type": "drone_flip",
	  "message0": "宙返り[ちゅうがえり] %1",
	  "args0": [
	    {
	      "type": "field_dropdown",
	      "name": "宙返り[ちゅうがえり]",
		  "options": [
		    ["前[まえ]","flip_f"],
		    ["後[うしろ]","flip_b"],
		    ["左[ひだり]","flip_l"],
		    ["右[みぎ]","flip_r"]
		  ]
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 30,
	  "tooltip": "宙返り[ちゅうがえり]",
	  "helpUrl": "",
	});
  },
};
