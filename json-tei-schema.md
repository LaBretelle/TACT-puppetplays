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

- activer désactiver les menu / éléments en fonction de la balise courrante

### Questions en vrac / plugin livré par OMEKA

- ajout de l'élément none dans toute liste du formulaire...
  - c'est pour que si on a plusieurs attributs, ceux qu'on ne veut pas voir apparaître n'apparaissent pas
  - MAIS cas de `damage` qui a rend / agent / degree est-ce que ça a du sens de ne mettre que agent et pas degree ?

- certaines balises sont redondantes avec l'HTML du coup c'est pour ça que tit[t]le / t[t]able
  - engendre un problème d'affichage ? si oui alors ça doit être possible de definir un comportement d'affichage spécfifque en CSS pour toute balise de ce type incluse dans une class CSS parente genre "tei"

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
