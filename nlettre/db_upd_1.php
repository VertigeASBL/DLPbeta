ALTER TABLE `c_membres`
ADD   `protect` varchar(255) NOT NULL default '',
ADD   `passw` varchar(255) character set latin1 collate latin1_bin NOT NULL default '',
ADD   `acces` int(10) unsigned NOT NULL default '0',
ADD   `adrip` varchar(255) NOT NULL default '',
ADD   `temps` varchar(255) NOT NULL default '',
ADD   `admail` varchar(255) NOT NULL default '';

-- 
-- Contenu de la table `cmsprotect`
-- 

update `c_membres` set (`admid`, `protect`, `login`, `passw`, `acces`, `adrip`, `temps`, `admail`) VALUES (3, '0.74282004101465', 'phil', 0x17c39a3f4fc2a5c3af, 29491198, '0.91742220978944', '595272717', 'philippe@vertige.org');

SELECT id_membre,pseudo,MD5(CONCAT(DECODE(passw,'pw'),'1190633896')) as m1,MD5(DECODE(passw,'pw')) as m2 FROM c_membres WHERE pseudo='phil' AND acces & 2048<>0;

SELECT id_membre FROM c_membres WHERE pseudo='phil' AND MD5(CONCAT(DECODE(passw,'pw'),'1190631491'))='d09b8fe06f8ca96b8788ea25286f5277' AND acces & 2048<>0 OR protect='421aa90e079fa326'
SELECT id_membre FROM c_membres WHERE pseudo='phil' AND MD5(CONCAT(DECODE(passw,'pw'),'1190633896'))='60e975cfa0a03ea7627ec3127ff1be92' AND acces & 2048<>0 OR protect='421aa90e079fa326'
2 phil 72a44435231ac8430eeb3fefb1ff261a 442b6746ab6f4ae6a9ac1989963cd09d 
2 phil b5ed4c5b38b5a890c33a137f204021b5 442b6746ab6f4ae6a9ac1989963cd09d 

SELECT admid,login,MD5(CONCAT(DECODE(passw,'pw'),'1190634057')) AS m1,MD5(DECODE(passw,'pw'),'1190634057')) FROM cmsprotect WHERE login='phil' AND ='bac396e5e81f4e8cc7411c01169aea70' AND acces & 2048<>0 OR protect='421aa90e079fa326'
