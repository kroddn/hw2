
-- Selektiere alle Städte, in denen sich herrenlose Truppen befinden und die Städe
-- selbst aber NICHT herrenlos sind.
SELECT cityunit.*, city.owner AS cityowner FROM cityunit LEFT JOIN city ON city.id = cityunit.city WHERE city IN
(
SELECT cu.city 
FROM cityunit cu 
LEFT JOIN city c on c.id = cu.city 
WHERE cu.owner IS NULL AND c.owner IS NOT NULL
)
ORDER BY cityunit.city, cityunit.unit, cityunit.owner


