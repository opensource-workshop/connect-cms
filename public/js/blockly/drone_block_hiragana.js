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
		"message0": "とぶ",
		"inputsInline": true,
		"previousStatement": null,
		"nextStatement": null,
		"colour": 315,
		"tooltip": "とぶ",
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
        "message0": "おりる",
        "inputsInline": false,
        "previousStatement": null,
        "nextStatement": null,
        "colour": 315,
        "tooltip": "おりる",
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
	  "message0": "うえ(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 230,
	  "tooltip": "うえ",
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
	  "message0": "した(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 230,
	  "tooltip": "した",
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
	  "message0": "まえ(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "まえ",
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
	  "message0": "うしろ(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "うしろ",
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
	  "message0": "ひだり(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "ひだり",
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
	  "message0": "みぎ(cm) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 50
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 180,
	  "tooltip": "みぎ",
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
	  "message0": "ひだりまわり(角度) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 90
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 90,
	  "tooltip": "ひだりまわり",
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
	  "message0": "みぎまわり(角度) %1",
	  "args0": [
	    {
	      "type": "field_number",
	      "name": "NAME",
	      "value": 90
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 90,
	  "tooltip": "みぎまわり",
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
	  "message0": "ちゅうがえり %1",
	  "args0": [
	    {
	      "type": "field_dropdown",
	      "name": "ちゅうがえり",
		  "options": [
		    ["まえ","flip_f"],
		    ["うしろ","flip_b"],
		    ["ひだり","flip_l"],
		    ["みぎ","flip_r"]
		  ]
	    }
	  ],
	  "inputsInline": true,
	  "previousStatement": null,
	  "nextStatement": null,
	  "colour": 30,
	  "tooltip": "ちゅうがえり",
	  "helpUrl": "",
	});
  },
};

Blockly.Blocks.drone_loop = {
  /**
   * Block for Loop.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
		"type": "drone_loop",
		"message0": "%1 かい %2 くりかえし %3",
		"args0": [
			{
				"type": "field_number",
				"name": "NAME",
				"value": 0
			},
			{
				"type": "input_dummy"
			},
			{
				"type": "input_statement",
				"name": "NAME",
				"check": "Number"
			}
		],
		"previousStatement": null,
		"nextStatement": null,
		"colour": 230,
		"tooltip": "",
		"helpUrl": ""
	});
  },
};