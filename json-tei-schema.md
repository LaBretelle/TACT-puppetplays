# JSON - TEI - SCHEMA

> comment on organise les données de définition TEI pour Tiny / Projet



### Dans l'idée

- chaque projet choisi un schema prédéfini
- il faut créer des fichiers json pour chaque schema.
- a l'initilisation de tiny il faut aussi charger le bon SCHEMA
  - pour éviter de l'asynchronne on pourrait enregistrer les schema en base
	- récupérer le json dans le controller
	- l'envoyer à la vue dans une balise
	- récupérer en javascript la valeur de cette balise...

### brouillon de schema

```json


{
	"elements": [
		{
			"tag": "string",
			"label": "chaine_de_trad",
			"help": "chaine_de_trad",
			"module": "string (teiName)",
			"icon" : "string",			
			"selfClosed": boolean,
			"attributes": [
				{
					"key": "string",
					"type": "string",
					"required": boolean,
					"label": "chaine_de_trad",
					"help": "chaine_de_trad",
					"values": [
						"value1",
						"value2",
						...
					]
				}

			],
			"childrens": [
				"tag1",
				"tag2",
				...
			]

		},
		{},
		...



	],
	"modules": [
		"teiName": "string",
		...
	]
}
```

```JSON

{
	"elements": [{
			"tag": "handShift",
			"label": "transcr_handshift",
			"help": "transcr_handshift_help",
			"module": "transcr",
			"icon": "handShift",
			"selfClosed": true,
			"attributes": [{
					"key": "rend",
					"type": "text",
					"required": false,
					"label": "attr_rend",
					"help": "attr_rend_help",
					"values": []
				}

			],
			"childrens": []
		},
		{

			"tag": "damage",
			"label": "transcr_damage",
			"help": "transcr_damage_help",
			"module": "transcr",
			"icon": "damage",
			"selfClosed": false,
			"attributes": [{
					"key": "rend",
					"type": "text",
					"required": false,
					"label": "attr_rend",
					"help": "attr_rend_help",
					"values": []
				},
				{
					"key": "agent",
					"type": "enumerated",
					"required": false,
					"label": "attr_agent",
					"help": "attr_agent_help",
					"values": [
						"rubbing", "mildew", "smoke"
					]
				},
				{
					"key": "degree",
					"type": "enumerated",
					"required": false,
					"label": "attr_degree",
					"help": "attr_degree_help",
					"values": [
						"a lot", "a bit", "medium"

					]
				}
			],
			"childrens": [
				"handShift",
				"rhyme"
			]
		},
		{

			"tag": "rhyme",
			"label": "verse_rhyme",
			"help": "verse_rhyme_help",
			"module": "verse",
			"icon": "rhyme",
			"selfClosed": false,
			"attributes": [{
				"key": "label",
				"type": "text",
				"required": true,
				"label": "attr_label",
				"help": "attr_label_help",
				"values": []
			}],
			"childrens": [
				"handShift",
				"damage"
			]

		}



	],
	"modules": [
		"transcr",
		"verse"
	]
}



 ```
