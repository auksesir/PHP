SELECT (CASE WHEN moduleResult >= 70 THEN '1st'
            WHEN moduleResult >= 60 THEN '2.1'
            WHEN moduleResult >= 50 THEN '2.2'
            WHEN moduleResult >= 45 THEN '3rd'
            WHEN moduleResult >= 40 THEN 'Pass'
			ELSE 'Fail'
	   END) AS Statistic, count(CASE WHEN moduleResult >= 70 THEN '1st'
            WHEN moduleResult >= 60 THEN '2.1'
            WHEN moduleResult >= 50 THEN '2.2'
            WHEN moduleResult >= 45 THEN '3rd'
            WHEN moduleResult >= 40 THEN 'Pass'
			ELSE 'Fail'
	   END) as Number
FROM moduleResults
WHERE moduleCode = 'jv'
GROUP BY Statistic;